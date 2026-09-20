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