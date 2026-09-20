<?php

/**
 * BillingService — gathers billable completed services for a patient
 * (consultations, lab tests, dispensed medicines, ward stays) and turns
 * them into itemized invoices. Every source is billed exactly once via
 * the unique (source_type, source_id) constraint on invoice_items.
 */

declare(strict_types=1);

class BillingService
{
    /**
     * All unbilled (not yet invoiced) completed services for a patient.
     * @return array<int, array{type:string,source_id:int,description:string,quantity:float,unit_price:float}>
     */
    public static function unbilledItems(int $patientId): array
    {
        $items = [];

        // 1. Consultation fees — completed appointments
        $stmt = db()->prepare(
            'SELECT a.id, a.appointment_date, du.name AS doctor_name
             FROM appointments a
             JOIN doctors d ON d.id = a.doctor_id
             JOIN users du ON du.id = d.user_id
             WHERE a.patient_id = ? AND a.status = "completed"
             AND NOT EXISTS (SELECT 1 FROM invoice_items ii WHERE ii.source_type = "consultation" AND ii.source_id = a.id)
             ORDER BY a.appointment_date'
        );
        $stmt->execute([$patientId]);
        foreach ($stmt->fetchAll() as $row) {
            $items[] = [
                'type'       => 'consultation',
                'source_id'  => (int) $row['id'],
                'description' => 'Consultation fee — Dr. ' . $row['doctor_name'] . ' (' . date('M j, Y', strtotime($row['appointment_date'])) . ')',
                'quantity'   => 1.0,
                'unit_price' => FEE_CONSULTATION,
            ];
        }

        // 2. Lab test fees — completed lab requests
        $stmt = db()->prepare(
            'SELECT lr.id, lr.test_name, lr.completed_at
             FROM lab_requests lr
             WHERE lr.patient_id = ? AND lr.status = "completed"
             AND NOT EXISTS (SELECT 1 FROM invoice_items ii WHERE ii.source_type = "lab" AND ii.source_id = lr.id)
             ORDER BY lr.completed_at'
        );
        $stmt->execute([$patientId]);
        foreach ($stmt->fetchAll() as $row) {
            $items[] = [
                'type'        => 'lab',
                'source_id'   => (int) $row['id'],
                'description' => 'Lab test — ' . $row['test_name'],
                'quantity'    => 1.0,
                'unit_price'  => FEE_LAB_TEST,
            ];
        }

        // 3. Medicine costs — dispensed prescriptions (real inventory price)
        $stmt = db()->prepare(
            'SELECT pr.id, pr.quantity, pr.dispensed_at, m.name AS medicine_name, m.unit_price
             FROM prescriptions pr
             JOIN medicines m ON m.id = pr.medicine_id
             WHERE pr.patient_id = ? AND pr.status = "dispensed"
             AND NOT EXISTS (SELECT 1 FROM invoice_items ii WHERE ii.source_type = "medicine" AND ii.source_id = pr.id)
             ORDER BY pr.dispensed_at'
        );
        $stmt->execute([$patientId]);
        foreach ($stmt->fetchAll() as $row) {
            $items[] = [
                'type'        => 'medicine',
                'source_id'   => (int) $row['id'],
                'description' => 'Medicine — ' . $row['medicine_name'],
                'quantity'    => (float) $row['quantity'],
                'unit_price'  => (float) $row['unit_price'],
            ];
        }

        // 4. Ward / bed charges — discharged admissions (per admitted day)
        $stmt = db()->prepare(
            'SELECT ad.id, ad.admission_date, ad.discharge_date, w.name AS ward_name, b.bed_number
             FROM admissions ad
             JOIN wards w ON w.id = ad.ward_id
             JOIN beds b ON b.id = ad.bed_id
             WHERE ad.patient_id = ? AND ad.status = "discharged"
             AND NOT EXISTS (SELECT 1 FROM invoice_items ii WHERE ii.source_type = "ward" AND ii.source_id = ad.id)
             ORDER BY ad.admission_date'
        );
        $stmt->execute([$patientId]);
        foreach ($stmt->fetchAll() as $row) {
            $days = max(1, (int) ((strtotime($row['discharge_date']) - strtotime($row['admission_date'])) / 86400) + 1);
            $from = date('M j', strtotime($row['admission_date']));
            $until = date('M j, Y', strtotime($row['discharge_date']));
            $items[] = [
                'type'        => 'ward',
                'source_id'   => (int) $row['id'],
                'description' => 'Ward stay — ' . $row['ward_name'] . ' (bed ' . $row['bed_number'] . '), ' . $from . ' to ' . $until . ', ' . $days . ' day(s)',
                'quantity'    => (float) $days,
                'unit_price'  => FEE_WARD_DAILY,
            ];
        }

        return $items;
    }

    public static function hasUnbilledItems(int $patientId): bool
    {
        return count(self::unbilledItems($patientId)) > 0;
    }

    /**
     * Create an itemized invoice for all of a patient's unbilled services.
     * @return array{ok:bool,invoice_id:int|null,message:string}
     */
    public static function createInvoiceForPatient(int $patientId, int $generatedBy, ?string $notes = null): array
    {
        if (Patient::findById($patientId) === false) {
            return ['ok' => false, 'invoice_id' => null, 'message' => 'Patient not found.'];
        }

        $items = self::unbilledItems($patientId);
        if (!$items) {
            return ['ok' => false, 'invoice_id' => null, 'message' => 'There are no unbilled services for this patient.'];
        }

        $invoiceId = Invoice::create($patientId, $generatedBy, $notes);

        $subtotal = 0.0;
        foreach ($items as $item) {
            Invoice::addItem($invoiceId, $item['description'], $item['unit_price'], $item['type'], $item['source_id'], $item['quantity']);
            $subtotal += $item['unit_price'] * $item['quantity'];
        }
        Invoice::setTotal($invoiceId, round($subtotal, 2), round($subtotal, 2));
        Invoice::recalc($invoiceId);

        return ['ok' => true, 'invoice_id' => $invoiceId, 'message' => 'Invoice generated.'];
    }

    /**
     * Record a payment against an invoice (cash offline or Telebirr online)
     * and refresh the invoice status. Amount must not exceed the balance.
     * @return array{ok:bool,payment_id:int|null,message:string}
     */
    public static function recordPayment(
        int $invoiceId,
        float $amount,
        string $method,
        int $recordedBy,
        ?string $reference = null,
        ?string $telebirrTxnNo = null,
        ?string $callbackPayload = null,
        string $status = 'success'
    ): array {
        $invoice = Invoice::findById($invoiceId);
        if ($invoice === false) {
            return ['ok' => false, 'payment_id' => null, 'message' => 'Invoice not found.'];
        }
        if (!in_array($method, Payment::METHODS, true)) {
            return ['ok' => false, 'payment_id' => null, 'message' => 'Invalid payment method.'];
        }
        if ($amount <= 0) {
            return ['ok' => false, 'payment_id' => null, 'message' => 'Payment amount must be positive.'];
        }
        if ($amount - 0.001 > (float) $invoice['due_amount']) {
            return ['ok' => false, 'payment_id' => null, 'message' => 'Amount exceeds the outstanding balance.'];
        }

        $paymentId = Payment::create([
            'invoice_id'        => $invoiceId,
            'amount'            => $amount,
            'method'            => $method,
            'status'            => $status,
            'reference'         => $reference,
            'paid_by'           => $recordedBy,
            'telebirr_txn_no'   => $telebirrTxnNo,
            'callback_payload'  => $callbackPayload,
        ]);

        if ($status === 'success') {
            Invoice::recalc($invoiceId);
        }
        return ['ok' => true, 'payment_id' => $paymentId, 'message' => 'Payment recorded.'];
    }
}