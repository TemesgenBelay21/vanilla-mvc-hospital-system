<?php

/**
 * PatientController — registration (receptionist), searchable list and
 * profile. The list and profile are shared between Admin and Receptionist.
 */

declare(strict_types=1);

class PatientController
{
    /** Full registration form — receptionist only. */
    public function create(): void
    {
        require_role('receptionist');
        view('patients/create');
    }

    /** Store a newly registered patient (receptionist only). */
    public function store(): void
    {
        require_role('receptionist');
        csrf_check();

        $input = [
            'name'     => trim($_POST['name'] ?? ''),
            'email'    => strtolower(trim($_POST['email'] ?? '')),
            'phone'    => trim($_POST['phone'] ?? ''),
            'dob'      => trim($_POST['date_of_birth'] ?? ''),
            'gender'   => trim($_POST['gender'] ?? ''),
            'address'  => trim($_POST['address'] ?? ''),
            'ec_name'  => trim($_POST['emergency_contact_name'] ?? ''),
            'ec_phone' => trim($_POST['emergency_contact_phone'] ?? ''),
            'blood'    => trim($_POST['blood_type'] ?? ''),
            'allergies'=> trim($_POST['allergies'] ?? ''),
        ];

        $errors = $this->validate($input);
        if ($errors) {
            foreach ($errors as $error) {
                flash('error', $error);
            }
            keep_old($input);
            redirect('/receptionist/patients/create');
        }

        $password = 'Patient@123';
        $userId = User::create([
            'role'     => 'patient',
            'name'     => $input['name'],
            'email'    => $input['email'],
            'password' => $password,
            'phone'    => $input['phone'] ?: null,
        ]);
        Patient::createFor($userId, [
            'date_of_birth'          => $input['dob'] ?: null,
            'gender'                 => $input['gender'] ?: null,
            'address'                => $input['address'] ?: null,
            'emergency_contact_name' => $input['ec_name'] ?: null,
            'emergency_contact_phone'=> $input['ec_phone'] ?: null,
            'blood_type'             => $input['blood'] ?: null,
            'allergies'              => $input['allergies'] ?: null,
        ]);

        flash('success', 'Patient "' . $input['name'] . '" registered. Temporary password is ' . $password . ' (email delivery arrives in a later phase).');
        redirect('/receptionist/patients');
    }

    /** Searchable list — admin and receptionist (guards redirect others). */
    public function index(): void
    {
        $user   = require_role('admin', 'receptionist');
        $q      = trim($_GET['q'] ?? '');
        $patients = Patient::search($q);

        view('patients/index', [
            'patients' => $patients,
            'q'        => $q,
            'isAdmin'  => $user['role'] === 'admin',
            'roleHome' => $user['role'] === 'receptionist' ? '/receptionist' : '/admin',
        ]);
    }

    /** Patients in a doctor's care — doctor only. */
    public function doctorIndex(): void
    {
        $user   = require_role('doctor');
        $doctor = Doctor::findByUserId((int) $user['id']);
        $q      = trim($_GET['q'] ?? '');

        view('patients/doctor-index', [
            'patients' => Patient::forDoctor((int) $doctor['id'], $q),
            'q'        => $q,
        ]);
    }

    public function profile(array $params): void
    {
        $user    = require_role('admin', 'receptionist', 'doctor');
        $patient = Patient::findById((int) $params['id']);

        if ($patient === false) {
            flash('error', 'Patient not found.');
            redirect('/' . $user['role'] . '/patients');
        }

        $isDoctor = $user['role'] === 'doctor';
        if ($isDoctor) {
            $doctor = Doctor::findByUserId((int) $user['id']);
            if (!Patient::inDoctorCare((int) $doctor['id'], (int) $patient['id'])) {
                flash('error', 'You can only view the records of patients under your care.');
                redirect('/doctor/patients');
            }
        }

        $showClinical = $user['role'] !== 'receptionist';
        $patientUserId = (int) $patient['user_id'];

        view('patients/profile', [
            'patient'       => $patient,
            'isAdmin'       => $user['role'] === 'admin',
            'isDoctor'      => $isDoctor,
            'canPrescribe'  => $isDoctor,
            'showClinical'  => $showClinical,
            'admissions'    => Admission::forPatient($patientUserId),
            'prescriptions' => $showClinical ? Prescription::forPatient($patientUserId) : [],
            'labs'          => $showClinical ? LabRequest::forPatient($patientUserId) : [],
            'roleHome'      => $isDoctor ? '/doctor' : ($user['role'] === 'receptionist' ? '/receptionist' : '/admin'),
        ]);
    }

    /** Basic validation shared by self-registration and receptionist forms. */
    public function validate(array $in): array
    {
        $errors = [];

        if (mb_strlen($in['name'] ?? '') < 3) {
            $errors[] = 'Full name must be at least 3 characters.';
        }
        if (!filter_var($in['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid email address is required.';
        } elseif (User::findByEmail($in['email']) !== false) {
            $errors[] = 'That email address is already in use.';
        }
        if (!empty($in['dob']) && (!strtotime($in['dob']) || strtotime($in['dob']) > strtotime('today'))) {
            $errors[] = 'Date of birth is invalid (cannot be in the future).';
        }
        if (!empty($in['gender']) && !in_array($in['gender'], GENDERS, true)) {
            $errors[] = 'Invalid gender selection.';
        }
        if (!empty($in['blood']) && !in_array($in['blood'], BLOOD_TYPES, true)) {
            $errors[] = 'Invalid blood type.';
        }

        return $errors;
    }
}