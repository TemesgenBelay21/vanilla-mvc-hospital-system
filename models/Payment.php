<?php

/**
 * Payment model — cash (offline) and Telebirr (online) payments
 * against an invoice.
 */

declare(strict_types=1);

class Payment
{
    public const METHODS = ['cash', 'telebirr'];
    public const STATUSES = ['pending', 'success', 'failed'];

    public static function create(array $d): int
    {
        $stmt = db()->prepare(
            'INSERT INTO payments (invoice_id, amount, method, status, reference, paid_by, transaction_date, telebirr_txn_no, callback_payload)
             VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?)'
        );
        $stmt->execute([
            $d['invoice_id'],
            $d['amount'],
            $d['method'],
            $d['status'] ?? 'success',
            $d['reference'] ?? null,
            $d['paid_by'] ?? null,
            $d['telebirr_txn_no'] ?? null,
            $d['callback_payload'] ?? null,
        ]);
        return (int) db()->lastInsertId();
    }

    public static function findById(int $id)
    {
        $stmt = db()->prepare(
            'SELECT p.*, i.invoice_number, p2.name AS patient_name
             FROM payments p
             JOIN invoices i ON i.id = p.invoice_id
             JOIN patients p2 ON p2.id = i.patient_id
             WHERE p.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: false;
    }

    /** Payments with invoice + patient context (accountant list). */
    public static function recent(int $limit = 20): array
    {
        $stmt = db()->prepare(
            'SELECT p.*, i.invoice_number, u.name AS patient_name, pu.name AS recorded_by_name
             FROM payments p
             JOIN invoices i ON i.id = p.invoice_id
             JOIN patients pt ON pt.id = i.patient_id
             JOIN users u ON u.id = pt.user_id
             LEFT JOIN users pu ON pu.id = p.paid_by
             ORDER BY p.transaction_date DESC, p.id DESC
             LIMIT ?'
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public static function sumBetween(string $from, string $to): float
    {
        $stmt = db()->prepare(
            'SELECT COALESCE(SUM(amount), 0) FROM payments
             WHERE status = "success" AND transaction_date >= ? AND transaction_date <= ?'
        );
        $stmt->execute([$from . ' 00:00:00', $to . ' 23:59:59']);
        return (float) $stmt->fetchColumn();
    }

    public static function countOf(): int
    {
        return (int) db()->query('SELECT COUNT(*) FROM payments WHERE status = "success"')->fetchColumn();
    }
}