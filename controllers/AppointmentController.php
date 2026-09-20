<?php

/**
 * AppointmentController — booking flow (patient + receptionist) and the
 * slot/doctor JSON lookups that drive the booking form.
 */

declare(strict_types=1);

class AppointmentController
{
    /** Shared booking form. Patient books for themselves, receptionist on behalf of a patient. */
    public function create(): void
    {
        $user     = require_role('patient', 'receptionist');
        $selected = 0;

        if ($user['role'] === 'patient') {
            $patient = Patient::findByUserId((int) $user['id']);
            if ($patient === false) {
                flash('error', 'Your patient profile was not found. Contact the front desk.');
                redirect('/patient');
            }
            $selected = (int) $patient['id'];
        } else {
            $selected = (int) ($_GET['patient'] ?? 0);
            if ($selected > 0 && Patient::findById($selected) === false) {
                flash('error', 'That patient does not exist.');
                redirect('/receptionist/appointments/book');
            }
        }

        view('appointments/book', [
            'departments' => Department::all(),
            'patients'    => $user['role'] === 'receptionist' ? Patient::search('') : [],
            'selected'    => $selected,
            'role'        => $user['role'],
        ]);
    }

    /** Create the appointment. */
    public function store(): void
    {
        $user = require_role('patient', 'receptionist');
        csrf_check();

        if ($user['role'] === 'patient') {
            $patient = Patient::findByUserId((int) $user['id']);
            if ($patient === false) {
                flash('error', 'Your patient profile was not found.');
                redirect('/patient');
            }
            $patientId = (int) $patient['id'];
        } else {
            $patientId = (int) ($_POST['patient_id'] ?? 0);
            if (Patient::findById($patientId) === false) {
                flash('error', 'Please select the patient this appointment is for.');
                redirect('/receptionist/appointments/book');
            }
        }

        $input = [
            'department_id' => (int) ($_POST['department_id'] ?? 0),
            'doctor_id'     => (int) ($_POST['doctor_id'] ?? 0),
            'date'          => trim($_POST['date'] ?? ''),
            'start_time'    => trim($_POST['start_time'] ?? ''),
            'notes'         => trim($_POST['patient_notes'] ?? ''),
        ];

        $error = $this->validateBooking($input, $patientId);
        if ($error !== null) {
            flash('error', $error);
            $this->backToBooking($user['role'], $patientId);
        }

        if (Appointment::isSlotTaken($input['doctor_id'], $input['date'], $input['start_time'])) {
            flash('error', 'That time slot has just been taken. Please choose another.');
            $this->backToBooking($user['role'], $patientId);
        }

        $id = Appointment::createAppointment([
            'patient_id'    => $patientId,
            'doctor_id'     => $input['doctor_id'],
            'department_id' => $input['department_id'],
            'date'          => $input['date'],
            'start_time'    => $input['start_time'],
            'patient_notes' => $input['notes'] ?: null,
        ]);

        if ($id === false) {
            flash('error', 'That time slot has just been taken. Please choose another.');
            $this->backToBooking($user['role'], $patientId);
        }

        flash('success', 'Appointment requested. It will appear once approved by the doctor.');
        redirect($user['role'] === 'patient' ? '/patient/appointments' : '/receptionist/appointments');
    }

    /** JSON: active doctors in a department (drives the booking form). */
    public function doctorsByDepartment(): void
    {
        require_login();
        $departmentId = (int) ($_GET['department_id'] ?? 0);
        if (Department::findById($departmentId) === false) {
            json_response(['doctors' => []], 404);
        }
        json_response(['doctors' => Doctor::byDepartment($departmentId)]);
    }

    /** JSON: available slots for a doctor on a date (drives the booking form). */
    public function slots(): void
    {
        require_login();
        $doctorId = (int) ($_GET['doctor_id'] ?? 0);
        $date     = trim($_GET['date'] ?? '');

        if (Doctor::findById($doctorId) === false || preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) !== 1) {
            json_response(['slots' => []], 400);
        }

        $raw  = Appointment::availableSlots($doctorId, $date);
        $slots = array_map(static function (string $time) {
            return ['start' => $time, 'end' => time_to($time), 'label' => date('g:i A', strtotime($time))];
        }, $raw);

        json_response(['slots' => $slots]);
    }

    /** Validate a booking against availability and booking rules. Returns error message or null. */
    private function validateBooking(array $in, int $patientId): ?string
    {
        $department = Department::findById($in['department_id'] ?? 0);
        if ($department === false) {
            return 'Please select a department.';
        }

        $doctor = Doctor::findById($in['doctor_id'] ?? 0);
        if ($doctor === false || (int) $doctor['department_id'] !== (int) $in['department_id']) {
            return 'Please select a doctor who belongs to the chosen department.';
        }
        if ($doctor['status'] !== 'active') {
            return 'That doctor is not currently active.';
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $in['date'] ?? '') !== 1) {
            return 'Please pick a valid appointment date.';
        }
        if (strtotime($in['date'] . ' 23:59:59') <= time()) {
            return 'Appointments cannot be booked in the past.';
        }

        if (!in_array($in['start_time'] ?? '', Availability::allowedTimes(), true)) {
            return 'Please pick a valid time slot.';
        }
        if (!Availability::slotExists((int) $doctor['id'], (int) date('w', strtotime($in['date'])), $in['start_time'])) {
            return 'The doctor has no availability at that time.';
        }

        return null;
    }

    /**
     * Load the appointment the current user is allowed to act on.
     * Doctors can only act on their own appointments; patients on their own.
     */
    private function loadOwnedAppointment(int $id, array $roles)
    {
        $user = require_role(...$roles);
        $app  = Appointment::findById($id);

        if ($app === false) {
            flash('error', 'Appointment not found.');
            redirect($this->backPath($user['role']));
        }
        if ($user['role'] === 'doctor') {
            $doctor = Doctor::findByUserId((int) $user['id']);
            if ((int) $app['doctor_id'] !== (int) $doctor['id']) {
                flash('error', 'You cannot manage another doctor\'s appointments.');
                redirect($this->backPath('doctor'));
            }
        }
        if ($user['role'] === 'patient') {
            $patient = Patient::findByUserId((int) $user['id']);
            if ((int) $app['patient_id'] !== (int) $patient['id']) {
                flash('error', 'You cannot manage another patient\'s appointment.');
                redirect($this->backPath('patient'));
            }
        }
        return [$user, $app];
    }

    /** Role-specific appointment lists. */
    public function index(): void
    {
        $user = require_login();

        if ($user['role'] === 'doctor') {
            $doctor = Doctor::findByUserId((int) $user['id']);
            $rows   = Appointment::forDoctor((int) $doctor['id']);

            $byDate = [];
            foreach ($rows as $row) {
                $byDate[$row['appointment_date']][] = $row;
            }

            view('appointments/doctor-index', ['byDate' => $byDate, 'doctor' => $doctor]);
            return;
        }

        if ($user['role'] === 'receptionist') {
            $status = trim($_GET['status'] ?? '');
            $date   = trim($_GET['date'] ?? '');

            view('appointments/receptionist-index', [
                'rows'     => Appointment::allForReceptionist($status, $date),
                'statuses' => APPOINTMENT_STATUSES,
                'status'   => $status,
                'date'     => $date,
            ]);
            return;
        }

        if ($user['role'] === 'patient') {
            $patient = Patient::findByUserId((int) $user['id']);
            $rows    = Appointment::forPatient((int) $patient['id']);

            $upcoming = array_filter($rows, static function ($r) {
                return ($r['appointment_date'] . ' ' . $r['start_time']) >= date('Y-m-d H:i:s')
                    && in_array($r['status'], ['pending', 'approved', 'rescheduled'], true);
            });
            $upcomingIds = array_column($upcoming, 'id');
            $history = array_filter($rows, static function ($r) use ($upcomingIds) {
                return !in_array((int) $r['id'], $upcomingIds, true);
            });

            view('appointments/patient-index', [
                'upcoming' => $upcoming,
                'history'  => $history,
            ]);
            return;
        }

        redirect('/login');
    }

    /** Redirect back to the correct booking form. */
    private function backToBooking(string $role, int $patientId): void
    {
        if ($role === 'receptionist') {
            redirect('/receptionist/appointments/book?patient=' . $patientId);
        }
        redirect('/patient/book');
    }

    // ---------------------------------------------------------------
    // Management actions
    // ---------------------------------------------------------------

    public function approve(array $params): void
    {
        csrf_check();
        [$user, $app] = $this->loadOwnedAppointment((int) $params['id'], ['doctor', 'receptionist']);

        if (!in_array($app['status'], ['pending', 'rescheduled'], true)) {
            flash('error', 'Only pending or rescheduled appointments can be approved.');
            redirect($this->backPath($user['role']));
        }

        Appointment::updateStatus((int) $app['id'], 'approved', 'Approved by ' . ($user['role'] === 'doctor' ? 'doctor' : 'receptionist') . '.');
        flash('success', 'Appointment approved.');
        redirect($this->backPath($user['role']));
    }

    public function reject(array $params): void
    {
        csrf_check();
        [$user, $app] = $this->loadOwnedAppointment((int) $params['id'], ['doctor', 'receptionist']);

        if (!in_array($app['status'], ['pending', 'approved', 'rescheduled'], true)) {
            flash('error', 'This appointment can no longer be rejected.');
            redirect($this->backPath($user['role']));
        }

        Appointment::updateStatus((int) $app['id'], 'rejected', 'Rejected by ' . ($user['role'] === 'doctor' ? 'doctor' : 'receptionist') . '.');
        flash('success', 'Appointment rejected.');
        redirect($this->backPath($user['role']));
    }

    public function complete(array $params): void
    {
        csrf_check();
        [$user, $app] = $this->loadOwnedAppointment((int) $params['id'], ['doctor']);

        if ($app['status'] !== 'approved') {
            flash('error', 'Only approved appointments can be marked completed.');
            redirect('/doctor/appointments');
        }

        Appointment::updateStatus((int) $app['id'], 'completed', 'Completed by doctor.');
        flash('success', 'Appointment marked as completed.');
        redirect('/doctor/appointments');
    }

    public function cancel(array $params): void
    {
        csrf_check();
        [$user, $app] = $this->loadOwnedAppointment((int) $params['id'], ['patient', 'receptionist']);

        if (!in_array($app['status'], ['pending', 'approved', 'rescheduled'], true)) {
            flash('error', 'This appointment can no longer be cancelled.');
            redirect($this->backPath($user['role']));
        }

        Appointment::updateStatus((int) $app['id'], 'rejected', 'Cancelled by ' . ($user['role'] === 'patient' ? 'patient' : 'receptionist') . '.');
        flash('success', 'Appointment cancelled.');
        redirect($this->backPath($user['role']));
    }

    public function rescheduleForm(array $params): void
    {
        [$user, $app] = $this->loadOwnedAppointment((int) $params['id'], ['doctor', 'receptionist']);

        $doctor = Doctor::findById((int) $app['doctor_id']);

        view('appointments/reschedule', [
            'app'    => $app,
            'doctor' => $doctor,
            'role'   => $user['role'],
        ]);
    }

    public function reschedule(array $params): void
    {
        csrf_check();
        [$user, $app] = $this->loadOwnedAppointment((int) $params['id'], ['doctor', 'receptionist']);

        if (!in_array($app['status'], ['pending', 'approved', 'rescheduled'], true)) {
            flash('error', 'This appointment can no longer be rescheduled.');
            redirect($this->backPath($user['role']));
        }

        $input = [
            'department_id' => (int) $app['department_id'],
            'doctor_id'     => (int) $app['doctor_id'],
            'date'          => trim($_POST['date'] ?? ''),
            'start_time'    => trim($_POST['start_time'] ?? ''),
        ];

        $error = $this->validateBooking($input, (int) $app['patient_id']);
        if ($error !== null) {
            flash('error', $error);
            redirect($user['role'] . '/appointments/' . (int) $app['id'] . '/reschedule');
        }
        if (Appointment::isSlotTaken((int) $app['doctor_id'], $input['date'], $input['start_time'], (int) $app['id'])) {
            flash('error', 'That time slot is already taken. Please choose another.');
            redirect($user['role'] . '/appointments/' . (int) $app['id'] . '/reschedule');
        }

        $ok = Appointment::reschedule((int) $app['id'], $input['date'], $input['start_time'], 'Rescheduled to ' . $input['date'] . ' ' . format_time($input['start_time']) . '.');
        if (!$ok) {
            flash('error', 'That time slot has just been taken. Please try another.');
            redirect($user['role'] . '/appointments/' . (int) $app['id'] . '/reschedule');
        }

        flash('success', 'Appointment rescheduled.');
        redirect($this->backPath($user['role']));
    }

    private function backPath(string $role): string
    {
        if ($role === 'doctor') {
            return '/doctor/appointments';
        }
        if ($role === 'patient') {
            return '/patient/appointments';
        }
        return '/receptionist/appointments';
    }
}