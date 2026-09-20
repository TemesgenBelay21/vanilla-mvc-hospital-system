<?php

/**
 * Bed model — bed management within a ward.
 */

declare(strict_types=1);

class Bed
{
    public static function findById(int $id)
    {
        $stmt = db()->prepare('SELECT * FROM beds WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: false;
    }

    /** Adds a bed; returns false when the bed number is already used in the ward. */
    public static function create(int $wardId, string $bedNumber): bool
    {
        $check = db()->prepare('SELECT id FROM beds WHERE ward_id = ? AND bed_number = ?');
        $check->execute([$wardId, trim($bedNumber)]);
        if ($check->fetch()) {
            return false;
        }

        $stmt = db()->prepare('INSERT INTO beds (ward_id, bed_number, status) VALUES (?, ?, "free")');
        $stmt->execute([$wardId, trim($bedNumber)]);
        return true;
    }

    /** Removes a bed; returns false when it is occupied. */
    public static function delete(int $id): bool
    {
        $bed = self::findById($id);
        if (!$bed || $bed['status'] === 'occupied') {
            return false;
        }

        $stmt = db()->prepare('DELETE FROM beds WHERE id = ? AND status = "free"');
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    /** Free beds in a ward, for the admission form. */
    public static function freeBedsOf(int $wardId): array
    {
        $stmt = db()->prepare(
            'SELECT id, bed_number FROM beds
             WHERE ward_id = ? AND status = "free"
             ORDER BY bed_number'
        );
        $stmt->execute([$wardId]);
        return $stmt->fetchAll();
    }
}