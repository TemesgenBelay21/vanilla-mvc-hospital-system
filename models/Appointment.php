<?php

/**
 * Appointment model — booking queries, stats and slot checks.
 */

declare(strict_types=1);

class Appointment
{
    /** Full appointment row with patient + doctor names. */
    public static function findById(int $id)
    {
        $stmt = db()->prepare(
            'SELECT a.*,
                    puser.name AS patient_name,
                    p.date_of_birth AS patient_dob, p.gender AS patient_gender,
                    duser.name AS doctor_name,
                    dep.name AS department_name
             FROM appointments a
             JOIN patients        p     ON p.id = a.patient_id
             JOIN users           puser ON puser.id = p.user_id
             JOIN doctors         d     ON d.id = a.doctor_id
             JOIN users           duser ON duser.id = d.user_id
             JOIN departments     dep   ON dep.id = a.department_id
             WHERE a.id = ?
             LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: false;
    }

    public static function countByStatus(string $status, ?int $doctorId = null): int
    {
        $sql = 'SELECT COUNT(*) FROM appointments WHERE status = ?';
        $params = [$status];
        if ($doctorId !== null) {
            $sql .= ' AND doctor_id = ?';
            $params[] = $doctorId;
        }
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public static function countToday(?int $doctorId = null): int
    {
        $sql = 'SELECT COUNT(*) FROM appointments WHERE appointment_date = CURDATE()';
        $params = [];
        if ($doctorId !== null) {
            $sql .= ' AND doctor_id = ?';
            $params[] = $doctorId;
        }
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }
}