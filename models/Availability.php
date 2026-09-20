<?php

/**
 * Availability model — a doctor's recurring weekly 30-minute slots.
 * day_of_week: 0 = Sunday … 6 = Saturday.
 */

declare(strict_types=1);

class Availability
{
    public static function allForDoctor(int $doctorId): array
    {
        $stmt = db()->prepare(
            'SELECT id, day_of_week, start_time
             FROM availabilities
             WHERE doctor_id = ?
             ORDER BY day_of_week, start_time'
        );
        $stmt->execute([$doctorId]);
        return $stmt->fetchAll();
    }

    public static function slotExists(int $doctorId, int $dayOfWeek, string $time): bool
    {
        $stmt = db()->prepare(
            'SELECT COUNT(*) FROM availabilities WHERE doctor_id = ? AND day_of_week = ? AND start_time = ?'
        );
        $stmt->execute([$doctorId, $dayOfWeek, $time]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public static function create(int $doctorId, int $dayOfWeek, string $time): bool
    {
        try {
            $stmt = db()->prepare(
                'INSERT INTO availabilities (doctor_id, day_of_week, start_time) VALUES (?, ?, ?)'
            );
            $stmt->execute([$doctorId, $dayOfWeek, $time]);
            return true;
        } catch (PDOException $e) {
            if ((int) $e->getCode() === 23000) {
                return false; // duplicate slot
            }
            throw $e;
        }
    }

    public static function delete(int $id, int $doctorId): bool
    {
        $stmt = db()->prepare('DELETE FROM availabilities WHERE id = ? AND doctor_id = ?');
        $stmt->execute([$id, $doctorId]);
        return $stmt->rowCount() > 0;
    }

    /** Allowed slot start times: 08:00–17:30 at 30-minute steps. */
    public static function allowedTimes(): array
    {
        $times = [];
        for ($t = strtotime('08:00'); $t < strtotime('18:00'); $t += SLOT_MINUTES * 60) {
            $times[] = date('H:i:s', $t);
        }
        return $times;
    }
}