<?php

/**
 * Doctor model — doctor profile records tied to doctor users.
 */

declare(strict_types=1);

class Doctor
{
    public static function findById(int $id)
    {
        $stmt = db()->prepare(
            'SELECT d.*, u.name, u.email, u.phone, u.photo, u.status, dep.name AS department_name
             FROM doctors d
             JOIN users u ON u.id = d.user_id
             JOIN departments dep ON dep.id = d.department_id
             WHERE d.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: false;
    }

    public static function findByUserId(int $userId)
    {
        $stmt = db()->prepare(
            'SELECT d.*, u.name, u.email, u.phone, u.photo, u.status, dep.name AS department_name
             FROM doctors d
             JOIN users u ON u.id = d.user_id
             JOIN departments dep ON dep.id = d.department_id
             WHERE d.user_id = ?'
        );
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: false;
    }

    public static function allWithUser(): array
    {
        $stmt = db()->query(
            'SELECT d.*, u.email, u.phone, u.photo, u.status,
                    dep.name AS department_name
             FROM doctors d
             JOIN users u ON u.id = d.user_id
             JOIN departments dep ON dep.id = d.department_id
             ORDER BY u.name'
        );
        return $stmt->fetchAll();
    }

    public static function byDepartment(int $departmentId): array
    {
        $stmt = db()->prepare(
            'SELECT d.id, u.name, u.photo, d.specialization
             FROM doctors d
             JOIN users u ON u.id = d.user_id
             WHERE d.department_id = ? AND u.status = "active"
             ORDER BY u.name'
        );
        $stmt->execute([$departmentId]);
        return $stmt->fetchAll();
    }

    public static function allActive(): array
    {
        $stmt = db()->query(
            'SELECT d.id, u.name, d.specialization, dep.name AS department_name
             FROM doctors d
             JOIN users u ON u.id = d.user_id
             JOIN departments dep ON dep.id = d.department_id
             WHERE u.status = "active"
             ORDER BY u.name'
        );
        return $stmt->fetchAll();
    }

    public static function countOf(): int
    {
        return (int) db()->query('SELECT COUNT(*) FROM doctors')->fetchColumn();
    }

    public static function createFor(int $userId, int $departmentId, string $specialization, ?string $qualification): int
    {
        $stmt = db()->prepare(
            'INSERT INTO doctors (user_id, department_id, specialization, qualification)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$userId, $departmentId, $specialization, $qualification]);
        return (int) db()->lastInsertId();
    }

    public static function updateProfile(int $id, int $departmentId, string $specialization, ?string $qualification): void
    {
        $stmt = db()->prepare(
            'UPDATE doctors SET department_id = ?, specialization = ?, qualification = ? WHERE id = ?'
        );
        $stmt->execute([$departmentId, $specialization, $qualification, $id]);
    }
}