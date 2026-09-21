<?php

/**
 * Prescription model — prescriptions written against the pharmacy inventory.
 */

declare(strict_types=1);

class Prescription
{
    public static function create(
        int $patientId,
        int $doctorId,
        int $medicineId,
        string $dosage,
        string $frequency,
        string $duration,
        int $quantity,
        ?string $notes
    ): void {
        $stmt = db()->prepare(
            'INSERT INTO prescriptions
                (patient_id, doctor_id, medicine_id, dosage, frequency, duration, quantity, notes, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, "pending")'
        );
        $stmt->execute([$patientId, $doctorId, $medicineId, $dosage, $frequency, $duration, $quantity, $notes]);
    }

    /** History for a patient (latest first), by patient id. */
    public static function forPatient(int $patientId): array
    {
        $stmt = db()->prepare(
            'SELECT pr.*, m.name AS medicine_name, m.unit_price,
                    du.name AS doctor_name, pu.name AS dispensed_by_name
             FROM prescriptions pr
             JOIN medicines m ON m.id = pr.medicine_id
             JOIN patients p ON p.id = pr.patient_id
             JOIN doctors d ON d.id = pr.doctor_id
             JOIN users du ON du.id = d.user_id
             LEFT JOIN users pu ON pu.id = pr.dispensed_by
             WHERE p.id = ?
             ORDER BY pr.prescribed_at DESC, pr.id DESC'
        );
        $stmt->execute([$patientId]);
        return $stmt->fetchAll();
    }

    public static function pendingCount(): int
    {
        return (int) db()->query(
            'SELECT COUNT(*) FROM prescriptions WHERE status = "pending"'
        )->fetchColumn();
    }

    /** Pending prescriptions with the patient, medicine and doctor — pharmacy queue. */
    public static function pendingList(): array
    {
        return db()->query(
            'SELECT pr.*, m.name AS medicine_name, m.stock_quantity, m.unit_price,
                    p.name AS patient_name, p.phone, p.date_of_birth, du.name AS doctor_name
             FROM prescriptions pr
             JOIN medicines m ON m.id = pr.medicine_id
             JOIN patients p ON p.id = pr.patient_id
             JOIN doctors d ON d.id = pr.doctor_id
             JOIN users du ON du.id = d.user_id
             WHERE pr.status = "pending"
             ORDER BY pr.prescribed_at ASC, pr.id ASC'
        )->fetchAll();
    }

    public static function findById(int $id)
    {
        $stmt = db()->prepare(
            'SELECT pr.*, m.name AS medicine_name, m.stock_quantity, m.unit_price, m.low_stock_threshold,
                    p.name AS patient_name, p.phone, p.date_of_birth, du.name AS doctor_name
             FROM prescriptions pr
             JOIN medicines m ON m.id = pr.medicine_id
             JOIN patients p ON p.id = pr.patient_id
             JOIN doctors d ON d.id = pr.doctor_id
             JOIN users du ON du.id = d.user_id
             WHERE pr.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: false;
    }

    /**
     * Dispenses a pending prescription by deducting stock in one transaction.
     * Returns true on success or an error message string.
     */
    public static function dispense(int $id, int $dispensedByUserId)
    {
        $db = db();
        $db->beginTransaction();

        try {
            $stmt = $db->prepare(
                'SELECT pr.*, m.name AS medicine_name, m.stock_quantity
                 FROM prescriptions pr
                 JOIN medicines m ON m.id = pr.medicine_id
                 WHERE pr.id = ? AND pr.status = "pending" FOR UPDATE'
            );
            $stmt->execute([$id]);
            $row = $stmt->fetch();

            if (!$row) {
                $db->rollBack();
                return 'This prescription is no longer pending.';
            }
            if ((int) $row['stock_quantity'] < (int) $row['quantity']) {
                $db->rollBack();
                return 'Not enough stock — only ' . (int) $row['stock_quantity'] . ' units of ' . e($row['medicine_name'] ?? 'this medicine') . ' available.';
            }

            $mark = $db->prepare(
                'UPDATE prescriptions
                 SET status = "dispensed", dispensed_by = ?, dispensed_at = NOW()
                 WHERE id = ?'
            );
            $mark->execute([$dispensedByUserId, $id]);

            $stock = $db->prepare('UPDATE medicines SET stock_quantity = stock_quantity - ? WHERE id = ?');
            $stock->execute([(int) $row['quantity'], (int) $row['medicine_id']]);

            $db->commit();
            return true;
        } catch (PDOException $e) {
            $db->rollBack();
            throw $e;
        }
    }
}