<?php

/**
 * LabRequest model — laboratory request lifecycle: requested → in_progress → completed.
 */

declare(strict_types=1);

class LabRequest
{
    public static function create(
        int $patientId,
        int $doctorId,
        string $testName,
        string $priority,
        ?string $notes
    ): int {
        $stmt = db()->prepare(
            'INSERT INTO lab_requests (patient_id, doctor_id, test_name, notes, priority, status)
             VALUES (?, ?, ?, ?, ?, "requested")'
        );
        $stmt->execute([$patientId, $doctorId, $testName, $notes, $priority]);
        return (int) db()->lastInsertId();
    }

    public static function findById(int $id)
    {
        $stmt = db()->prepare(
            'SELECT lr.*, p.name AS patient_name, p.phone, p.date_of_birth,
                    du.name AS doctor_name, cu.name AS completed_by_name
             FROM lab_requests lr
             JOIN patients p ON p.id = lr.patient_id
             JOIN doctors d ON d.id = lr.doctor_id
             JOIN users du ON du.id = d.user_id
             LEFT JOIN users cu ON cu.id = lr.completed_by
             WHERE lr.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: false;
    }

    /** All requests with patient and doctor names, newest first. */
    public static function all(): array
    {
        return db()->query(
            'SELECT lr.*, p.name AS patient_name, du.name AS doctor_name
             FROM lab_requests lr
             JOIN patients p ON p.id = lr.patient_id
             JOIN doctors d ON d.id = lr.doctor_id
             JOIN users du ON du.id = d.user_id
             ORDER BY lr.requested_at DESC'
        )->fetchAll();
    }

    /** History for a patient (by patient id). */
    public static function forPatient(int $patientId): array
    {
        $stmt = db()->prepare(
            'SELECT lr.*, du.name AS doctor_name, cu.name AS completed_by_name
             FROM lab_requests lr
             JOIN patients p ON p.id = lr.patient_id
             JOIN doctors d ON d.id = lr.doctor_id
             JOIN users du ON du.id = d.user_id
             LEFT JOIN users cu ON cu.id = lr.completed_by
             WHERE p.id = ?
             ORDER BY lr.requested_at DESC, lr.id DESC'
        );
        $stmt->execute([$patientId]);
        return $stmt->fetchAll();
    }

    /** Moves a requested lab to in_progress. */
    public static function start(int $id): bool
    {
        $stmt = db()->prepare(
            'UPDATE lab_requests
             SET status = "in_progress", started_at = NOW()
             WHERE id = ? AND status = "requested"'
        );
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    /** Marks a lab completed with text result and/or result file. */
    public static function complete(int $id, int $completedByUserId, ?string $resultText, ?string $resultFile): bool
    {
        $stmt = db()->prepare(
            'UPDATE lab_requests
             SET status = "completed", result_text = ?, result_file = ?,
                 completed_at = NOW(), completed_by = ?
             WHERE id = ? AND status IN ("requested", "in_progress")'
        );
        $stmt->execute([$resultText, $resultFile, $completedByUserId, $id]);
        return $stmt->rowCount() > 0;
    }

    public static function counts(): array
    {
        $stmt = db()->query(
            'SELECT status, COUNT(*) AS n FROM lab_requests GROUP BY status'
        );
        $counts = ['requested' => 0, 'in_progress' => 0, 'completed' => 0];
        foreach ($stmt->fetchAll() as $row) {
            $counts[$row['status']] = (int) $row['n'];
        }
        return $counts;
    }
}