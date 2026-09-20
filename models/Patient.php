<?php

/**
 * Patient model — medical profile records tied to patient users.
 */

declare(strict_types=1);

class Patient
{
    public static function findByUserId(int $userId)
    {
        $stmt = db()->prepare(
            'SELECT p.*, u.name, u.email, u.phone, u.status, u.created_at AS account_created_at
             FROM patients p
             JOIN users u ON u.id = p.user_id
             WHERE p.user_id = ?'
        );
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: false;
    }

    public static function findById(int $id)
    {
        $stmt = db()->prepare(
            'SELECT p.*, u.name, u.email, u.phone, u.status, u.created_at AS account_created_at
             FROM patients p
             JOIN users u ON u.id = p.user_id
             WHERE p.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: false;
    }

    public static function createFor(int $userId, array $d): int
    {
        $stmt = db()->prepare(
            'INSERT INTO patients
                (user_id, date_of_birth, gender, address, emergency_contact_name,
                 emergency_contact_phone, blood_type, allergies)
             VALUES
                (:user_id, :dob, :gender, :address, :ec_name, :ec_phone, :blood, :allergies)'
        );
        $stmt->execute([
            ':user_id'   => $userId,
            ':dob'       => $d['date_of_birth'] ?? null,
            ':gender'    => $d['gender'] ?? null,
            ':address'   => $d['address'] ?? null,
            ':ec_name'   => $d['emergency_contact_name'] ?? null,
            ':ec_phone'  => $d['emergency_contact_phone'] ?? null,
            ':blood'     => $d['blood_type'] ?? null,
            ':allergies' => $d['allergies'] ?? null,
        ]);
        return (int) db()->lastInsertId();
    }

    public static function updateByUserId(int $userId, array $d): void
    {
        $stmt = db()->prepare(
            'UPDATE patients SET
                date_of_birth = :dob, gender = :gender, address = :address,
                emergency_contact_name = :ec_name, emergency_contact_phone = :ec_phone,
                blood_type = :blood, allergies = :allergies
             WHERE user_id = :user_id'
        );
        $stmt->execute([
            ':user_id'   => $userId,
            ':dob'       => $d['date_of_birth'] ?? null,
            ':gender'    => $d['gender'] ?? null,
            ':address'   => $d['address'] ?? null,
            ':ec_name'   => $d['emergency_contact_name'] ?? null,
            ':ec_phone'  => $d['emergency_contact_phone'] ?? null,
            ':blood'     => $d['blood_type'] ?? null,
            ':allergies' => $d['allergies'] ?? null,
        ]);
    }

    public static function updateUserFields(int $userId, array $d): void
    {
        User::update($userId, $d);
    }

    public static function countOf(): int
    {
        return (int) db()->query('SELECT COUNT(*) FROM patients')->fetchColumn();
    }
}