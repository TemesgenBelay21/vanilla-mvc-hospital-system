<?php

/**
 * Medicine model — pharmacy inventory lookups.
 */

declare(strict_types=1);

class Medicine
{
    public static function all(): array
    {
        return db()->query(
            'SELECT m.*, (m.stock_quantity <= m.low_stock_threshold) AS low_stock
             FROM medicines m
             ORDER BY m.name'
        )->fetchAll();
    }

    public static function findById(int $id)
    {
        $stmt = db()->prepare('SELECT * FROM medicines WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: false;
    }

    public static function findByName(string $name)
    {
        $stmt = db()->prepare('SELECT * FROM medicines WHERE name = ?');
        $stmt->execute([trim($name)]);
        return $stmt->fetch() ?: false;
    }

    public static function create(
        string $name,
        ?string $category,
        int $stockQuantity,
        float $unitPrice,
        ?string $expiryDate,
        ?string $supplier,
        int $lowStockThreshold
    ): void {
        $stmt = db()->prepare(
            'INSERT INTO medicines
                (name, category, stock_quantity, unit_price, expiry_date, supplier, low_stock_threshold)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([trim($name), $category, $stockQuantity, $unitPrice, $expiryDate, $supplier, $lowStockThreshold]);
    }

    public static function update(
        int $id,
        string $name,
        ?string $category,
        float $unitPrice,
        ?string $expiryDate,
        ?string $supplier,
        int $lowStockThreshold
    ): void {
        $stmt = db()->prepare(
            'UPDATE medicines
             SET name = ?, category = ?, unit_price = ?, expiry_date = ?, supplier = ?, low_stock_threshold = ?
             WHERE id = ?'
        );
        $stmt->execute([trim($name), $category, $unitPrice, $expiryDate, $supplier, $lowStockThreshold, $id]);
    }

    public static function restock(int $id, int $amount): void
    {
        $stmt = db()->prepare('UPDATE medicines SET stock_quantity = stock_quantity + ? WHERE id = ?');
        $stmt->execute([$amount, $id]);
    }

    /** Adds stock; returns false when the amount is not positive. */
    public static function addStock(int $id, int $amount): bool
    {
        if ($amount < 1) {
            return false;
        }
        self::restock($id, $amount);
        return true;
    }

    /** Removes stock atomically; returns false when there is not enough. */
    public static function withdraw(int $id, int $amount): bool
    {
        $stmt = db()->prepare(
            'UPDATE medicines
             SET stock_quantity = stock_quantity - ?
             WHERE id = ? AND stock_quantity >= ?'
        );
        $stmt->execute([$amount, $id, $amount]);
        return $stmt->rowCount() > 0;
    }

    /** Deletes a medicine; returns false when prescriptions reference it. */
    public static function delete(int $id): bool
    {
        $check = db()->prepare('SELECT COUNT(*) FROM prescriptions WHERE medicine_id = ?');
        $check->execute([$id]);
        if ((int) $check->fetchColumn() > 0) {
            return false;
        }

        $stmt = db()->prepare('DELETE FROM medicines WHERE id = ?');
        $stmt->execute([$id]);
        return true;
    }

    public static function countOf(): int
    {
        return (int) db()->query('SELECT COUNT(*) FROM medicines')->fetchColumn();
    }

    public static function lowStockCount(): int
    {
        return (int) db()->query(
            'SELECT COUNT(*) FROM medicines WHERE stock_quantity <= low_stock_threshold'
        )->fetchColumn();
    }
}