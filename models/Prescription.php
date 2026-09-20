<?php

/**
 * Prescription model — prescriptions written against the pharmacy inventory.
 */

declare(strict_types=1);

class Prescription
{
    public static function create(
        int $patientId,
        int $doctorId,
        int $medicineId,
        string $dosage,
        string $frequency,
        string $duration,
        int $quantity,
        ?string $notes
    ): void {
        $stmt = db()->prepare(
            'INSERT INTO prescriptions
                (patient_id, doctor_id, medicine_id, dosage, frequency, duration, quantity, notes, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, "pending")'
        );
        $stmt->execute([$patientId, $doctorId, $medicineId, $dosage, $frequency, $duration, $quantity, $notes]);
    }

    /** History for a patient (latest first), by patient user id. */
    public static function forPatient(int $patientUserId): array
    {
        $stmt = db()->prepare(
            'SELECT pr.*, m.name AS medicine_name, m.unit_price,
                    du.name AS doctor_name, pu.name AS dispensed_by_name
             FROM prescriptions pr
             JOIN medicines m ON m.id = pr.medicine_id
             JOIN patients p ON p.id = pr.patient_id
             JOIN doctors d ON d.id = pr.doctor_id
             JOIN users du ON du.id = d.user_id
             LEFT JOIN users pu ON pu.id = pr.dispensed_by
             WHERE p.user_id = ?
             ORDER BY pr.prescribed_at DESC, pr.id DESC'
        );
        $stmt->execute([$patientUserId]);
        return $stmt->fetchAll();
    }

    public static function pendingCount(): int
    {
        return (int) db()->query(
            'SELECT COUNT(*) FROM prescriptions WHERE status = "pending"'
        )->fetchColumn();
    }
}