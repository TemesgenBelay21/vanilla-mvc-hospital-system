<?php

/**
 * Department model.
 */

declare(strict_types=1);

class Department
{
    public static function all(): array
    {
        $stmt = db()->query(
            'SELECT d.*, (SELECT COUNT(*) FROM doctors x WHERE x.department_id = d.id) AS doctor_count
             FROM departments d
             ORDER BY d.name'
        );
        return $stmt->fetchAll();
    }

    public static function findById(int $id)
    {
        $stmt = db()->prepare('SELECT * FROM departments WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: false;
    }

    public static function findByName(string $name)
    {
        $stmt = db()->prepare('SELECT * FROM departments WHERE name = ?');
        $stmt->execute([$name]);
        return $stmt->fetch() ?: false;
    }

    public static function create(string $name, ?string $description): int
    {
        $stmt = db()->prepare('INSERT INTO departments (name, description) VALUES (?, ?)');
        $stmt->execute([trim($name), $description]);
        return (int) db()->lastInsertId();
    }

    public static function update(int $id, string $name, ?string $description): void
    {
        $stmt = db()->prepare('UPDATE departments SET name = ?, description = ? WHERE id = ?');
        $stmt->execute([trim($name), $description, $id]);
    }

    public static function delete(int $id): void
    {
        $stmt = db()->prepare('DELETE FROM departments WHERE id = ?');
        $stmt->execute([$id]);
    }

    public static function doctorCount(int $id): int
    {
        $stmt = db()->prepare('SELECT COUNT(*) FROM doctors WHERE department_id = ?');
        $stmt->execute([$id]);
        return (int) $stmt->fetchColumn();
    }

    public static function countOf(): int
    {
        return (int) db()->query('SELECT COUNT(*) FROM departments')->fetchColumn();
    }
}