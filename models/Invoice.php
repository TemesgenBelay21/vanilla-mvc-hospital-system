<?php

/**
 * Invoice model — billing records for a patient visit/admission.
 * Status lifecycle: unpaid -> (partially_paid)* -> paid, derived from
 * the sum of payments against the invoice total.
 */

declare(strict_types=1);

class Invoice
{
    public const STATUSES = ['unpaid', 'partially_paid', 'paid'];

    /** Invoice row with the patient name/user id. */
    public static function findById(int $id)
    {
        $stmt = db()->prepare(
            'SELECT i.*, p.id AS patient_id, u.name AS patient_name, u.email AS patient_email,
                    gu.name AS generated_by_name
             FROM invoices i
             JOIN patients p ON p.id = i.patient_id
             JOIN users u   ON u.id = p.user_id
             LEFT JOIN users gu ON gu.id = i.generated_by
             WHERE i.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: false;
    }

    public static function nextNumber(): string
    {
        $year = date('Y');
        $stmt = db()->query('SELECT invoice_number FROM invoices ORDER BY id DESC LIMIT 1');
        $last = $stmt->fetchColumn();
        $seq = 1;
        if ($last !== false && $last !== null) {
            if (preg_match('/-(\d+)$/', (string) $last, $m)) {
                $seq = ((int) $m[1]) + 1;
            }
        }
        return 'ALM-' . $year . '-' . str_pad((string) $seq, 6, '0', STR_PAD_LEFT);
    }

    public static function create(int $patientId, int $generatedBy, ?string $notes): int
    {
        $stmt = db()->prepare(
            'INSERT INTO invoices (invoice_number, patient_id, status, generated_by, notes)
             VALUES (?, ?, "unpaid", ?, ?)'
        );
        $stmt->execute([self::nextNumber(), $patientId, $generatedBy, $notes]);
        return (int) db()->lastInsertId();
    }

    /**
     * Add a line item. Returns false when the source was already billed
     * (unique source_type + source_id) — this guards double-billing.
     */
    public static function addItem(int $invoiceId, string $description, float $unitPrice, string $sourceType, int $sourceId, float $quantity = 1.0): bool
    {
        if (!in_array($sourceType, ['consultation', 'lab', 'medicine', 'ward'], true)) {
            return false;
        }
        try {
            $stmt = db()->prepare(
                'INSERT INTO invoice_items (invoice_id, description, quantity, unit_price, line_total, source_type, source_id)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([$invoiceId, $description, $quantity, $unitPrice, round($unitPrice * $quantity, 2), $sourceType, $sourceId]);
            return true;
        } catch (PDOException $e) {
            if ((int) $e->getCode() === 23000) {
                return false;
            }
            throw $e;
        }
    }

    public static function items(int $invoiceId): array
    {
        $stmt = db()->prepare('SELECT * FROM invoice_items WHERE invoice_id = ? ORDER BY id');
        $stmt->execute([$invoiceId]);
        return $stmt->fetchAll();
    }

    public static function payments(int $invoiceId): array
    {
        $stmt = db()->prepare(
            'SELECT p.*, u.name AS paid_by_name
             FROM payments p
             LEFT JOIN users u ON u.id = p.paid_by
             WHERE p.invoice_id = ?
             ORDER BY p.transaction_date DESC, p.id DESC'
        );
        $stmt->execute([$invoiceId]);
        return $stmt->fetchAll();
    }

    /** Recompute amount_paid + status from the payment rows. */
    public static function recalc(int $invoiceId): void
    {
        $stmt = db()->prepare(
            'SELECT COALESCE(SUM(amount), 0) AS total FROM payments WHERE invoice_id = ? AND status = "success"'
        );
        $stmt->execute([$invoiceId]);
        $paid = (float) $stmt->fetchColumn();

        $stmt = db()->prepare('SELECT total_amount FROM invoices WHERE id = ?');
        $stmt->execute([$invoiceId]);
        $total = (float) $stmt->fetchColumn();

        $status = $paid <= 0.001 ? 'unpaid' : ($paid + 0.001 >= $total ? 'paid' : 'partially_paid');

        $upd = db()->prepare(
            'UPDATE invoices SET amount_paid = ?, due_amount = GREATEST(total_amount - ?, 0), status = ? WHERE id = ?'
        );
        $upd->execute([$paid, $paid, $status, $invoiceId]);
    }

    public static function setTotal(int $invoiceId, float $subtotal, float $total): void
    {
        $stmt = db()->prepare('UPDATE invoices SET subtotal = ?, total_amount = ? WHERE id = ?');
        $stmt->execute([$subtotal, $total, $invoiceId]);
    }

    /** Filterable list for the accountant. */
    public static function all(string $status = '', string $q = '', string $from = '', string $to = ''): array
    {
        $sql = 'SELECT i.*, p.id AS patient_id, u.name AS patient_name, u.email AS patient_email
                FROM invoices i
                JOIN patients p ON p.id = i.patient_id
                JOIN users u   ON u.id = p.user_id
                WHERE 1 = 1';
        $params = [];

        if ($status !== '' && in_array($status, self::STATUSES, true)) {
            $sql .= ' AND i.status = ?';
            $params[] = $status;
        }
        if ($q !== '') {
            $sql .= ' AND (u.name LIKE ? OR i.invoice_number LIKE ?)';
            $params[] = '%' . $q . '%';
            $params[] = '%' . $q . '%';
        }
        if ($from !== '') {
            $sql .= ' AND i.generated_at >= ?';
            $params[] = $from . ' 00:00:00';
        }
        if ($to !== '') {
            $sql .= ' AND i.generated_at <= ?';
            $params[] = $to . ' 23:59:59';
        }

        $sql .= ' ORDER BY i.generated_at DESC, i.id DESC';
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function forPatient(int $patientUserId): array
    {
        $stmt = db()->prepare(
            'SELECT i.*, p.id AS patient_id
             FROM invoices i
             JOIN patients p ON p.id = i.patient_id
             WHERE p.user_id = ?
             ORDER BY i.generated_at DESC, i.id DESC'
        );
        $stmt->execute([$patientUserId]);
        return $stmt->fetchAll();
    }

    /** Outstanding (unpaid) total across all invoices. */
    public static function outstandingTotal(): float
    {
        return (float) db()->query('SELECT COALESCE(SUM(due_amount), 0) FROM invoices')->fetchColumn();
    }

    /** Outstanding (unpaid) balance for one patient (by user id). */
    public static function outstandingFor(int $patientUserId): float
    {
        $stmt = db()->prepare(
            'SELECT COALESCE(SUM(i.due_amount), 0)
             FROM invoices i
             JOIN patients p ON p.id = i.patient_id
             WHERE p.user_id = ?'
        );
        $stmt->execute([$patientUserId]);
        return (float) $stmt->fetchColumn();
    }

    public static function countByStatus(string $status): int
    {
        $stmt = db()->prepare('SELECT COUNT(*) FROM invoices WHERE status = ?');
        $stmt->execute([$status]);
        return (int) $stmt->fetchColumn();
    }

    public static function countOf(): int
    {
        return (int) db()->query('SELECT COUNT(*) FROM invoices')->fetchColumn();
    }
}