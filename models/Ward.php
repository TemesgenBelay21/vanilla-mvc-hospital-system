<?php

/**
 * Ward model — ward and bed queries.
 */

declare(strict_types=1);

class Ward
{
    public static function all(): array
    {
        $stmt = db()->query(
            'SELECT w.*, COUNT(b.id) AS bed_count,
                    COALESCE(SUM(b.status = "occupied"), 0) AS occupied_count
             FROM wards w
             LEFT JOIN beds b ON b.ward_id = w.id
             GROUP BY w.id
             ORDER BY w.name'
        );
        return $stmt->fetchAll();
    }

    public static function findById(int $id)
    {
        $stmt = db()->prepare(
            'SELECT w.*, COUNT(b.id) AS bed_count,
                    COALESCE(SUM(b.status = "occupied"), 0) AS occupied_count
             FROM wards w
             LEFT JOIN beds b ON b.ward_id = w.id
             WHERE w.id = ?
             GROUP BY w.id'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: false;
    }

    public static function bedsOf(int $wardId): array
    {
        $stmt = db()->prepare(
            'SELECT b.*, a.id AS admission_id, p.id AS patient_id, p.name AS patient_name
             FROM beds b
             LEFT JOIN admissions a ON a.bed_id = b.id AND a.status = "admitted"
             LEFT JOIN patients  p ON p.id = a.patient_id
             WHERE b.ward_id = ?
             ORDER BY b.bed_number'
        );
        $stmt->execute([$wardId]);
        return $stmt->fetchAll();
    }

    public static function create(string $name, ?string $description): int
    {
        $stmt = db()->prepare('INSERT INTO wards (name, description) VALUES (?, ?)');
        $stmt->execute([trim($name), $description]);
        return (int) db()->lastInsertId();
    }

    public static function update(int $id, string $name, ?string $description): void
    {
        $stmt = db()->prepare('UPDATE wards SET name = ?, description = ? WHERE id = ?');
        $stmt->execute([trim($name), $description, $id]);
    }

    /** Deletes a ward; returns false when it still has beds. */
    public static function delete(int $id): bool
    {
        $bed = db()->prepare('SELECT COUNT(*) FROM beds WHERE ward_id = ?');
        $bed->execute([$id]);
        if ((int) $bed->fetchColumn() > 0) {
            return false;
        }
        $stmt = db()->prepare('DELETE FROM wards WHERE id = ?');
        $stmt->execute([$id]);
        return true;
    }

    public static function findByName(string $name)
    {
        $stmt = db()->prepare('SELECT * FROM wards WHERE name = ?');
        $stmt->execute([trim($name)]);
        return $stmt->fetch() ?: false;
    }

    public static function countOf(): int
    {
        return (int) db()->query('SELECT COUNT(*) FROM wards')->fetchColumn();
    }
}