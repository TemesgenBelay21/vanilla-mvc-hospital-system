<?php

/**
 * Admission model — admissions, discharges and their business rules.
 */

declare(strict_types=1);

class Admission
{
    public static function findById(int $id)
    {
        $stmt = db()->prepare(
            'SELECT a.*, w.name AS ward_name, b.bed_number,
                    p.user_id AS patient_user_id, u.name AS patient_name,
                    u.phone, p.date_of_birth, p.gender, p.blood_type, p.address,
                    du.name AS doctor_name
             FROM admissions a
             JOIN wards  w ON w.id = a.ward_id
             JOIN beds   b ON b.id = a.bed_id
             JOIN patients p ON p.id = a.patient_id
             JOIN users   u ON u.id = p.user_id
             LEFT JOIN doctors d ON d.id = a.admitting_doctor_id
             LEFT JOIN users du ON du.id = d.user_id
             WHERE a.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: false;
    }

    /** The patient's active admission, if any. */
    public static function activeForPatient(int $patientUserId)
    {
        $stmt = db()->prepare(
            'SELECT a.* FROM admissions a
             JOIN patients p ON p.id = a.patient_id
             WHERE p.user_id = ? AND a.status = "admitted"
             ORDER BY a.id DESC LIMIT 1'
        );
        $stmt->execute([$patientUserId]);
        return $stmt->fetch() ?: false;
    }

    /** Full admission history for a patient (newest first). */
    public static function forPatient(int $patientUserId): array
    {
        $stmt = db()->prepare(
            'SELECT a.*, w.name AS ward_name, b.bed_number, du.name AS doctor_name
             FROM admissions a
             JOIN wards w ON w.id = a.ward_id
             JOIN beds b ON b.id = a.bed_id
             JOIN patients p ON p.id = a.patient_id
             LEFT JOIN doctors d ON d.id = a.admitting_doctor_id
             LEFT JOIN users du ON du.id = d.user_id
             WHERE p.user_id = ?
             ORDER BY a.admission_date DESC, a.id DESC'
        );
        $stmt->execute([$patientUserId]);
        return $stmt->fetchAll();
    }

    /** Active admissions in a ward (nurse ward view). */
    public static function forWard(int $wardId): array
    {
        $stmt = db()->prepare(
            'SELECT a.*, b.bed_number, u.name AS patient_name,
                    du.name AS doctor_name
             FROM admissions a
             JOIN beds b ON b.id = a.bed_id
             JOIN patients p ON p.id = a.patient_id
             JOIN users u ON u.id = p.user_id
             LEFT JOIN doctors d ON d.id = a.admitting_doctor_id
             LEFT JOIN users du ON du.id = d.user_id
             WHERE a.ward_id = ? AND a.status = "admitted"
             ORDER BY a.admission_date DESC'
        );
        $stmt->execute([$wardId]);
        return $stmt->fetchAll();
    }

    /** All active admissions, optionally narrowed to a set of wards. */
    public static function current(?array $wardIds = null): array
    {
        $where = 'a.status = "admitted"';
        $params = [];
        if ($wardIds !== null && count($wardIds) > 0) {
            $where .= ' AND a.ward_id IN (' . implode(',', array_fill(0, count($wardIds), '?')) . ')';
            $params = $wardIds;
        }

        $stmt = db()->prepare(
            'SELECT a.*, w.name AS ward_name, b.bed_number,
                    u.name AS patient_name, du.name AS doctor_name
             FROM admissions a
             JOIN wards w ON w.id = a.ward_id
             JOIN beds b ON b.id = a.bed_id
             JOIN patients p ON p.id = a.patient_id
             JOIN users u ON u.id = p.user_id
             LEFT JOIN doctors d ON d.id = a.admitting_doctor_id
             LEFT JOIN users du ON du.id = d.user_id
             WHERE ' . $where . '
             ORDER BY a.admission_date DESC'
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Admits a patient to a bed. Returns the new admission id on success,
     * or an error message string when the business rules forbid it.
     */
    public static function admit(int $patientId, int $wardId, int $bedId, ?int $doctorId, ?string $reason)
    {
        $db = db();
        $db->beginTransaction();

        try {
            $bed = $db->prepare('SELECT status FROM beds WHERE id = ? FOR UPDATE');
            $bed->execute([$bedId]);
            $bedRow = $bed->fetch();

            if (!$bedRow) {
                $db->rollBack();
                return 'That bed no longer exists.';
            }
            if ($bedRow['status'] !== 'free') {
                $db->rollBack();
                return 'That bed was just taken by another admission. Please pick a free bed.';
            }

            $check = $db->prepare(
                'SELECT COUNT(*) FROM admissions a
                 JOIN patients p ON p.id = a.patient_id
                 WHERE p.id = ? AND a.status = "admitted"'
            );
            $check->execute([$patientId]);
            if ((int) $check->fetchColumn() > 0) {
                $db->rollBack();
                return 'This patient already has an active admission. Discharge it first.';
            }

            $update = $db->prepare('UPDATE beds SET status = "occupied" WHERE id = ?');
            $update->execute([$bedId]);

            $insert = $db->prepare(
                'INSERT INTO admissions
                    (patient_id, ward_id, bed_id, admitting_doctor_id, admission_reason, admission_date, status)
                 VALUES (?, ?, ?, ?, ?, NOW(), "admitted")'
            );
            $insert->execute([$patientId, $wardId, $bedId, $doctorId, $reason]);

            $id = (int) $db->lastInsertId();
            $db->commit();
            return $id;
        } catch (PDOException $e) {
            $db->rollBack();
            if ($e->getCode() === '23000') {
                return 'That bed was just taken. Please choose another one.';
            }
            throw $e;
        }
    }

    /**
     * Discharges an admission. Returns true on success or an error message string.
     */
    public static function discharge(int $id, ?string $notes)
    {
        $db = db();
        $db->beginTransaction();

        try {
            $stmt = $db->prepare('SELECT id, bed_id FROM admissions WHERE id = ? AND status = "admitted" FOR UPDATE');
            $stmt->execute([$id]);
            $admission = $stmt->fetch();

            if (!$admission) {
                $db->rollBack();
                return 'This admission is not currently active.';
            }

            $update = $db->prepare(
                'UPDATE admissions SET status = "discharged", discharge_date = NOW(), discharge_notes = ?
                 WHERE id = ?'
            );
            $update->execute([$notes, $id]);

            $bed = $db->prepare('UPDATE beds SET status = "free" WHERE id = ?');
            $bed->execute([$admission['bed_id']]);

            $db->commit();
            return true;
        } catch (PDOException $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function activeCount(): int
    {
        return (int) db()->query('SELECT COUNT(*) FROM admissions WHERE status = "admitted"')->fetchColumn();
    }
}