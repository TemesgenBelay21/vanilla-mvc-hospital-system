<?php

/**
 * Patient model — staff-managed patient records.
 *
 * Patients are purely data: they never log in and have no account. Contact
 * details (name/email/phone) live on the record itself.
 */

declare(strict_types=1);

class Patient
{
    public static function findById(int $id)
    {
        $stmt = db()->prepare('SELECT * FROM patients WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: false;
    }

    public static function findByEmail(string $email)
    {
        $stmt = db()->prepare('SELECT id FROM patients WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: false;
    }

    public static function create(array $d): int
    {
        $stmt = db()->prepare(
            'INSERT INTO patients
                (name, email, phone, date_of_birth, gender, address, emergency_contact_name,
                 emergency_contact_phone, blood_type, allergies)
             VALUES
                (:name, :email, :phone, :dob, :gender, :address, :ec_name, :ec_phone, :blood, :allergies)'
        );
        $stmt->execute([
            ':name'      => trim($d['name']),
            ':email'     => ($d['email'] ?? '') !== '' ? strtolower(trim($d['email'])) : null,
            ':phone'     => ($d['phone'] ?? '') !== '' ? $d['phone'] : null,
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

    public static function update(int $id, array $d): void
    {
        $stmt = db()->prepare(
            'UPDATE patients SET
                name = :name, email = :email, phone = :phone,
                date_of_birth = :dob, gender = :gender, address = :address,
                emergency_contact_name = :ec_name, emergency_contact_phone = :ec_phone,
                blood_type = :blood, allergies = :allergies
             WHERE id = :id'
        );
        $stmt->execute([
            ':name'      => trim($d['name']),
            ':email'     => ($d['email'] ?? '') !== '' ? strtolower(trim($d['email'])) : null,
            ':phone'     => ($d['phone'] ?? '') !== '' ? $d['phone'] : null,
            ':dob'       => $d['date_of_birth'] ?? null,
            ':gender'    => $d['gender'] ?? null,
            ':address'   => $d['address'] ?? null,
            ':ec_name'   => $d['emergency_contact_name'] ?? null,
            ':ec_phone'  => $d['emergency_contact_phone'] ?? null,
            ':blood'     => $d['blood_type'] ?? null,
            ':allergies' => $d['allergies'] ?? null,
            ':id'        => $id,
        ]);
    }

    public static function search(string $term = ''): array
    {
        $term = trim($term);
        $stmt = db()->prepare(
            'SELECT p.id, p.name, p.email, p.phone, p.gender, p.blood_type, p.created_at
             FROM patients p
             WHERE (:term = "") OR p.id = :num OR p.name LIKE :like OR p.phone LIKE :like2
             ORDER BY p.name'
        );
        $stmt->execute([
            ':term'  => $term,
            ':num'   => (int) $term !== 0 ? (int) $term : -1,
            ':like'  => '%' . $term . '%',
            ':like2' => '%' . $term . '%',
        ]);
        return $stmt->fetchAll();
    }

    /** Patients who have ever consulted, treated, admitted, prescribed or
     *  requested labs for the given doctor — "patients in this doctor's care". */
    public static function forDoctor(int $doctorId, string $term = ''): array
    {
        $term = trim($term);
        $stmt = db()->prepare(
            'SELECT p.id, p.name, p.email, p.phone, p.gender, p.blood_type, p.created_at,
                    (SELECT MAX(ap.appointment_date)
                       FROM appointments ap WHERE ap.doctor_id = :did AND ap.patient_id = p.id) AS last_visit
             FROM patients p
             WHERE (
                    p.id IN (SELECT ap.patient_id FROM appointments ap WHERE ap.doctor_id = :did1)
                 OR p.id IN (SELECT ad.patient_id FROM admissions ad WHERE ad.admitting_doctor_id = :did2)
                 OR p.id IN (SELECT pr.patient_id FROM prescriptions pr WHERE pr.doctor_id = :did3)
                 OR p.id IN (SELECT lr.patient_id FROM lab_requests lr WHERE lr.doctor_id = :did4)
             )
             AND (:term = "" OR p.id = :num OR p.name LIKE :like OR p.phone LIKE :like2)
             ORDER BY p.name'
        );
        $stmt->execute([
            ':did'   => $doctorId, ':did1' => $doctorId, ':did2' => $doctorId,
            ':did3'  => $doctorId, ':did4' => $doctorId,
            ':term'  => $term,
            ':num'   => (int) $term !== 0 ? (int) $term : -1,
            ':like'  => '%' . $term . '%',
            ':like2' => '%' . $term . '%',
        ]);
        return $stmt->fetchAll();
    }

    /** Whether a doctor has any care relationship with the patient. */
    public static function inDoctorCare(int $doctorId, int $patientId): bool
    {
        $stmt = db()->prepare(
            'SELECT 1
             WHERE EXISTS (SELECT 1 FROM appointments ap WHERE ap.doctor_id = ? AND ap.patient_id = ?)
                OR EXISTS (SELECT 1 FROM admissions ad WHERE ad.admitting_doctor_id = ? AND ad.patient_id = ?)
                OR EXISTS (SELECT 1 FROM prescriptions pr WHERE pr.doctor_id = ? AND pr.patient_id = ?)
                OR EXISTS (SELECT 1 FROM lab_requests lr WHERE lr.doctor_id = ? AND lr.patient_id = ?)
             LIMIT 1'
        );
        $stmt->execute([$doctorId, $patientId, $doctorId, $patientId, $doctorId, $patientId, $doctorId, $patientId]);
        return $stmt->fetchColumn() !== false;
    }

    public static function countOf(): int
    {
        return (int) db()->query('SELECT COUNT(*) FROM patients')->fetchColumn();
    }
}
