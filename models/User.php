<?php

/**
 * User model — authentication and account queries.
 */

declare(strict_types=1);

class User
{
    public static function findById(int $id)
    {
        $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: false;
    }

    public static function findByEmail(string $email)
    {
        $stmt = db()->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: false;
    }

    public static function create(array $d): int
    {
        $stmt = db()->prepare(
            'INSERT INTO users (role, name, email, password, phone, photo, status)
             VALUES (:role, :name, :email, :password, :phone, :photo, :status)'
        );
        $stmt->execute([
            ':role'     => $d['role'],
            ':name'     => trim($d['name']),
            ':email'    => strtolower(trim($d['email'])),
            ':password' => password_hash($d['password'], PASSWORD_DEFAULT),
            ':phone'    => $d['phone'] ?? null,
            ':photo'    => $d['photo'] ?? null,
            ':status'   => $d['status'] ?? 'active',
        ]);
        return (int) db()->lastInsertId();
    }

    public static function update(int $id, array $d): void
    {
        $stmt = db()->prepare(
            'UPDATE users SET name = :name, email = :email, phone = :phone, photo = COALESCE(:photo, photo)
             WHERE id = :id'
        );
        $stmt->execute([
            ':name'   => trim($d['name']),
            ':email'  => strtolower(trim($d['email'])),
            ':phone'  => $d['phone'] ?? null,
            ':photo'  => $d['photo'] ?? null,
            ':id'     => $id,
        ]);
    }

    public static function updatePassword(int $id, string $password): void
    {
        $stmt = db()->prepare('UPDATE users SET password = ? WHERE id = ?');
        $stmt->execute([password_hash($password, PASSWORD_DEFAULT), $id]);
    }

    public static function setStatus(int $id, string $status): void
    {
        $stmt = db()->prepare('UPDATE users SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
    }

    public static function updateLastLogin(int $id): void
    {
        $stmt = db()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?');
        $stmt->execute([$id]);
    }

    public static function countByRole(string $role): int
    {
        $stmt = db()->prepare('SELECT COUNT(*) FROM users WHERE role = ?');
        $stmt->execute([$role]);
        return (int) $stmt->fetchColumn();
    }

    /** Doctors + receptionists with their doctor-profile details, for admin. */
    public static function staff(): array
    {
        $stmt = db()->query(
            'SELECT u.id, u.role, u.name, u.email, u.phone, u.photo, u.status, u.last_login_at,
                    d.specialization, d.qualification, d.department_id,
                    dep.name AS department_name
             FROM users u
             LEFT JOIN doctors d ON d.user_id = u.id
             LEFT JOIN departments dep ON dep.id = d.department_id
             WHERE u.role IN ("doctor", "receptionist")
             ORDER BY u.role, u.name'
        );
        return $stmt->fetchAll();
    }
}