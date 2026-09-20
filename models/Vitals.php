<?php

/**
 * Vitals model — vital signs recorded against an admission.
 */

declare(strict_types=1);

class Vitals
{
    public static function create(int $admissionId, int $recordedByUserId, array $values): void
    {
        $stmt = db()->prepare(
            'INSERT INTO vitals
                (admission_id, temperature, systolic, diastolic, heart_rate, notes, recorded_by, recorded_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW())'
        );
        $stmt->execute([
            $admissionId,
            $values['temperature'] !== '' ? (float) $values['temperature'] : null,
            $values['systolic']    !== '' ? (int) $values['systolic']      : null,
            $values['diastolic']   !== '' ? (int) $values['diastolic']     : null,
            $values['heart_rate']  !== '' ? (int) $values['heart_rate']    : null,
            $values['notes']       !== '' ? $values['notes']               : null,
            $recordedByUserId,
        ]);
    }

    public static function forAdmission(int $admissionId): array
    {
        $stmt = db()->prepare(
            'SELECT v.*, u.name AS recorded_by_name
             FROM vitals v
             JOIN admissions a ON a.id = v.admission_id
             LEFT JOIN users u ON u.id = v.recorded_by
             WHERE v.admission_id = ?
             ORDER BY v.recorded_at DESC, v.id DESC'
        );
        $stmt->execute([$admissionId]);
        return $stmt->fetchAll();
    }

    /** Latest vitals per admission, for a set of admissions (patient portal use). */
    public static function latestForAdmissions(array $admissionIds): array
    {
        if (!$admissionIds) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($admissionIds), '?'));
        $stmt = db()->prepare(
            'SELECT v.*, u.name AS recorded_by_name
             FROM vitals v
             LEFT JOIN users u ON u.id = v.recorded_by
             WHERE v.id IN (
                 SELECT MAX(v2.id)
                 FROM vitals v2
                 WHERE v2.admission_id IN (' . $placeholders . ')
                 GROUP BY v2.admission_id
             )
             ORDER BY v.recorded_at DESC'
        );
        $stmt->execute($admissionIds);
        return $stmt->fetchAll();
    }

    /** Latest vitals per admission for a set of wards (dashboard use). */
    public static function latestForWards(array $wardIds): array
    {
        $where = count($wardIds) > 0
            ? 'a.ward_id IN (' . implode(',', array_fill(0, count($wardIds), '?')) . ')'
            : '1 = 1';

        $stmt = db()->prepare(
            'SELECT v.*, u.name AS patient_name, a.id AS admission_id, w.name AS ward_name, b.bed_number
             FROM vitals v
             JOIN admissions a ON a.id = v.admission_id
             JOIN wards w ON w.id = a.ward_id
             JOIN beds b ON b.id = a.bed_id
             JOIN patients p ON p.id = a.patient_id
             JOIN users u ON u.id = p.user_id
             JOIN (
                 SELECT admission_id, MAX(recorded_at) AS max_at
                 FROM vitals GROUP BY admission_id
             ) latest ON latest.admission_id = v.admission_id AND latest.max_at = v.recorded_at
             WHERE v.id IN (
                 SELECT MAX(id) FROM vitals GROUP BY admission_id
             )
             AND a.status = "admitted"
             AND ' . $where . '
             ORDER BY v.recorded_at DESC'
        );
        $stmt->execute($wardIds);
        return $stmt->fetchAll();
    }
}