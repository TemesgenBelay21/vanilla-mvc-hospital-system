<?php

/**
 * Nurse model — nurse profile records and ward assignments.
 */

declare(strict_types=1);

class Nurse
{
    public static function findByUserId(int $userId)
    {
        $stmt = db()->prepare(
            'SELECT n.*, u.name, u.email, u.phone, u.status
             FROM nurses n
             JOIN users u ON u.id = n.user_id
             WHERE n.user_id = ?'
        );
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: false;
    }

    public static function findById(int $id)
    {
        $stmt = db()->prepare(
            'SELECT n.*, u.name, u.email, u.phone, u.status
             FROM nurses n
             JOIN users u ON u.id = n.user_id
             WHERE n.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: false;
    }

    public static function createFor(int $userId): int
    {
        $stmt = db()->prepare('INSERT INTO nurses (user_id) VALUES (?)');
        $stmt->execute([$userId]);
        return (int) db()->lastInsertId();
    }

    /** Wards this nurse is assigned to, with live bed counts. */
    public static function wardsFor(int $nurseId): array
    {
        $stmt = db()->prepare(
            'SELECT w.*, COUNT(b.id) AS bed_count,
                    COALESCE(SUM(b.status = "occupied"), 0) AS occupied_count
             FROM nurse_ward_assignments nwa
             JOIN wards w ON w.id = nwa.ward_id
             LEFT JOIN beds b ON b.ward_id = w.id
             WHERE nwa.nurse_id = ?
             GROUP BY w.id
             ORDER BY w.name'
        );
        $stmt->execute([$nurseId]);
        return $stmt->fetchAll();
    }

    public static function wardIdsFor(int $nurseId): array
    {
        $stmt = db()->prepare('SELECT ward_id FROM nurse_ward_assignments WHERE nurse_id = ?');
        $stmt->execute([$nurseId]);
        return array_map('intval', array_column($stmt->fetchAll(), 'ward_id'));
    }

    /** Replace the nurse's ward assignments entirely. */
    public static function assignWards(int $nurseId, array $wardIds): void
    {
        $stmt = db()->prepare('DELETE FROM nurse_ward_assignments WHERE nurse_id = ?');
        $stmt->execute([$nurseId]);

        $ins = db()->prepare('INSERT INTO nurse_ward_assignments (nurse_id, ward_id) VALUES (?, ?)');
        foreach ($wardIds as $wardId) {
            $ins->execute([$nurseId, (int) $wardId]);
        }
    }
}