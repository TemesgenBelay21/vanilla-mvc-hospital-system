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
                    p.name AS patient_name,
                    p.phone AS patient_phone, p.email AS patient_email,
                    p.date_of_birth AS patient_dob, p.gender AS patient_gender,
                    duser.name AS doctor_name,
                    dep.name AS department_name
             FROM appointments a
             JOIN patients        p     ON p.id = a.patient_id
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

    /** True when the given slot already has an appointment for the doctor. */
    public static function isSlotTaken(int $doctorId, string $date, string $time, int $excludeId = 0): bool
    {
        $stmt = db()->prepare(
            'SELECT COUNT(*) FROM appointments
             WHERE doctor_id = ? AND appointment_date = ? AND start_time = ? AND id <> ?'
        );
        $stmt->execute([$doctorId, $date, $time, $excludeId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Bookable slots for a doctor on a given date:
     * the doctor's weekly slots on that weekday, minus already-booked slots
     * (and minus past times when the date is today). Optionally excludes an
     * appointment id (used while rescheduling).
     */
    public static function availableSlots(int $doctorId, string $date, int $excludeId = 0): array
    {
        $dayOfWeek = (int) date('w', strtotime($date));

        $avail = db()->prepare(
            'SELECT start_time FROM availabilities WHERE doctor_id = ? AND day_of_week = ?'
        );
        $avail->execute([$doctorId, $dayOfWeek]);
        $allSlots = array_column($avail->fetchAll(), 'start_time');

        $booked = db()->prepare(
            'SELECT start_time FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND id <> ?'
        );
        $booked->execute([$doctorId, $date, $excludeId]);
        $taken = array_fill_keys(array_column($booked->fetchAll(), 'start_time'), true);

        $now = time();
        $isToday = $date === date('Y-m-d');

        $slots = [];
        foreach ($allSlots as $start) {
            if (isset($taken[$start])) {
                continue;
            }
            if ($isToday && strtotime($date . ' ' . $start) <= $now) {
                continue;
            }
            $slots[] = $start;
        }
        sort($slots);
        return $slots;
    }

    /**
     * Create an appointment. Returns the new id, or false when the slot was
     * taken concurrently (unique constraint on doctor/date/start_time).
     */
    public static function createAppointment(array $d)
    {
        try {
            $stmt = db()->prepare(
                'INSERT INTO appointments
                    (patient_id, doctor_id, department_id, appointment_date, start_time, end_time, patient_notes)
                 VALUES (:patient_id, :doctor_id, :department_id, :date, :start_time, :end_time, :notes)'
            );
            $stmt->execute([
                ':patient_id'     => $d['patient_id'],
                ':doctor_id'      => $d['doctor_id'],
                ':department_id'  => $d['department_id'],
                ':date'           => $d['date'],
                ':start_time'     => $d['start_time'],
                ':end_time'       => time_to($d['start_time']),
                ':notes'          => $d['patient_notes'] ?? null,
            ]);
            return (int) db()->lastInsertId();
        } catch (PDOException $e) {
            if ((int) $e->getCode() === 23000) {
                return false;
            }
            throw $e;
        }
    }

    /** Doctor's appointments (with patient names), latest first. */
    public static function forDoctor(int $doctorId): array
    {
        $stmt = db()->prepare(
            'SELECT a.*, p.name AS patient_name, p.id AS patient_id,
                    dep.name AS department_name
             FROM appointments a
             JOIN patients        p     ON p.id = a.patient_id
             JOIN departments     dep   ON dep.id = a.department_id
             WHERE a.doctor_id = ? AND a.appointment_date >= CURDATE()
             ORDER BY a.appointment_date, a.start_time'
        );
        $stmt->execute([$doctorId]);
        return $stmt->fetchAll();
    }

    /** All bookings for the receptionist, with optional status/date filters. */
    public static function allForReceptionist(string $status = '', string $date = '', int $doctorId = 0): array
    {
        $sql = 'SELECT a.*, p.name AS patient_name, p.phone AS patient_phone,
                       duser.name AS doctor_name, dep.name AS department_name
                FROM appointments a
                JOIN patients   p     ON p.id = a.patient_id
                JOIN doctors    d     ON d.id = a.doctor_id
                JOIN users      duser ON duser.id = d.user_id
                JOIN departments dep  ON dep.id = a.department_id
                WHERE 1 = 1';
        $params = [];

        if ($status !== '' && in_array($status, APPOINTMENT_STATUSES, true)) {
            $sql .= ' AND a.status = ?';
            $params[] = $status;
        }
        if ($date !== '') {
            $sql .= ' AND a.appointment_date = ?';
            $params[] = $date;
        }
        if ($doctorId > 0) {
            $sql .= ' AND a.doctor_id = ?';
            $params[] = $doctorId;
        }

        $sql .= ' ORDER BY a.appointment_date DESC, a.start_time';

        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function forPatient(int $patientId): array
    {
        $stmt = db()->prepare(
            'SELECT a.*, duser.name AS doctor_name, dep.name AS department_name
             FROM appointments a
             JOIN doctors      d     ON d.id = a.doctor_id
             JOIN users        duser ON duser.id = d.user_id
             JOIN departments  dep   ON dep.id = a.department_id
             WHERE a.patient_id = ?
             ORDER BY a.appointment_date DESC, a.start_time DESC'
        );
        $stmt->execute([$patientId]);
        return $stmt->fetchAll();
    }

    public static function updateStatus(int $id, string $status, ?string $staffNotes = null): void
    {
        $stmt = db()->prepare(
            'UPDATE appointments
             SET status = :status, staff_notes = COALESCE(:notes, staff_notes)
             WHERE id = :id'
        );
        $stmt->execute([
            ':status' => $status,
            ':notes'  => $staffNotes,
            ':id'     => $id,
        ]);
    }

    public static function reschedule(int $id, string $date, string $startTime, ?string $staffNotes = null): bool
    {
        try {
            $stmt = db()->prepare(
                'UPDATE appointments
                 SET appointment_date = :date, start_time = :start, end_time = :end,
                     status = :status, staff_notes = COALESCE(:notes, staff_notes)
                 WHERE id = :id'
            );
            $stmt->execute([
                ':date'   => $date,
                ':start'  => $startTime,
                ':end'    => time_to($startTime),
                ':status' => 'rescheduled',
                ':notes'  => $staffNotes,
                ':id'     => $id,
            ]);
            return true;
        } catch (PDOException $e) {
            if ((int) $e->getCode() === 23000) {
                return false;
            }
            throw $e;
        }
    }
}