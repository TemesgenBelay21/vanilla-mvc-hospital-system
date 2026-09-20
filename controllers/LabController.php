<?php

/**
 * LabController — laboratory requests (doctor) and the lab technician
 * workspace with guarded result file delivery.
 */

declare(strict_types=1);

class LabController
{
    private const ALLOWED_EXT = ['pdf', 'png', 'jpg', 'jpeg'];
    private const MAX_BYTES = 2 * 1024 * 1024;

    /** New lab request form — doctor only. */
    public function create(array $params): void
    {
        $user   = require_role('doctor');
        $doctor = Doctor::findByUserId((int) $user['id']);
        $patient = Patient::findById((int) $params['id']);

        if ($patient === false || !Patient::inDoctorCare((int) $doctor['id'], (int) $patient['id'])) {
            flash('error', 'You can only request labs for patients under your care.');
            redirect('/doctor/patients');
        }

        view('lab/requests/create', [
            'patient' => $patient,
        ]);
    }

    public function store(array $params): void
    {
        $user = require_role('doctor');
        csrf_check();

        $doctor = Doctor::findByUserId((int) $user['id']);
        $patient = Patient::findById((int) $params['id']);

        if ($patient === false || !Patient::inDoctorCare((int) $doctor['id'], (int) $patient['id'])) {
            flash('error', 'You can only request labs for patients under your care.');
            redirect('/doctor/patients');
        }

        $testName = trim($_POST['test_name'] ?? '');
        $priority = trim($_POST['priority'] ?? 'normal');
        $notes    = trim($_POST['notes'] ?? '') ?: null;

        if (mb_strlen($testName) < 2) {
            flash('error', 'A test name is required.');
            redirect('/doctor/patients/' . (int) $patient['id'] . '/lab/new');
        }
        if (!in_array($priority, ['normal', 'urgent'], true)) {
            $priority = 'normal';
        }

        LabRequest::create((int) $patient['id'], (int) $doctor['id'], $testName, $priority, $notes);

        flash('success', 'Lab request sent to the laboratory for ' . e($patient['name']) . '.');
        redirect('/doctor/patients/' . (int) $patient['id'] . '/profile');
    }

    /** All requests — lab technician only. */
    public function index(): void
    {
        require_role('lab_technician');

        view('lab/requests/index', [
            'requests' => LabRequest::all(),
            'counts'   => LabRequest::counts(),
        ]);
    }

    public function show(array $params): void
    {
        require_role('lab_technician');

        $request = LabRequest::findById((int) $params['id']);
        if ($request === false) {
            flash('error', 'Lab request not found.');
            redirect('/lab/requests');
        }

        view('lab/requests/show', [
            'request' => $request,
        ]);
    }

    public function start(array $params): void
    {
        require_role('lab_technician');
        csrf_check();

        if (LabRequest::start((int) $params['id'])) {
            flash('success', 'Request marked in progress.');
        } else {
            flash('error', 'Only requested labs can be started.');
        }
        redirect('/lab/requests/' . (int) $params['id']);
    }

    public function complete(array $params): void
    {
        $user = require_role('lab_technician');
        csrf_check();

        $id         = (int) $params['id'];
        $resultText = trim($_POST['result_text'] ?? '') ?: null;
        $file       = $_FILES['result_file'] ?? null;

        $request = LabRequest::findById($id);
        if ($request === false) {
            flash('error', 'Lab request not found.');
            redirect('/lab/requests');
        }

        $error = null;
        $savedName = null;

        if ($file && isset($file['error']) && $file['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $error = 'File upload failed.';
            } elseif ($file['size'] > self::MAX_BYTES) {
                $error = 'Result file must be under 2MB.';
            } else {
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, self::ALLOWED_EXT, true)) {
                    $error = 'Result file must be PDF, PNG or JPG.';
                } else {
                    $dir = APP_ROOT . '/storage/lab';
                    $savedName = 'result_' . $id . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

                    if (!is_dir($dir)) {
                        mkdir($dir, 0777, true);
                    }
                    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $savedName)) {
                        $error = 'Could not save the result file.';
                        $savedName = null;
                    }
                }
            }
        }

        if ($resultText === null && $savedName === null) {
            $error = $error ?: 'Add a result text or attach a result file.';
        }

        if ($error !== null) {
            flash('error', $error);
            redirect('/lab/requests/' . $id);
        }

        if (LabRequest::complete($id, (int) $user['id'], $resultText, $savedName)) {
            $patient = Patient::findById((int) $request['patient_id']);
            if ($patient !== false) {
                notify_and_mail((int) $patient['user_id'], 'Lab result ready',
                    'Your result for ' . $request['test_name'] . ' is ready to view in your medical records.',
                    '/patient/medical', 'lab-result-ready', [
                        'patient_name' => $patient['name'],
                        'test_name'    => $request['test_name'],
                        'result_date'  => date('Y-m-d H:i:s'),
                        'result_link'  => url('/patient/medical'),
                    ]);
            }
            flash('success', 'Lab result recorded and shared with the requesting doctor.');
        } else {
            flash('error', 'This lab is already completed.');
        }
        redirect('/lab/requests/' . $id);
    }

    /** Guarded download of a result file. */
    public function download(array $params): void
    {
        $user = require_role('admin', 'lab_technician', 'doctor', 'patient');

        $request = LabRequest::findById((int) $params['id']);
        if ($request === false || $request['result_file'] === null) {
            flash('error', 'No result file for this request.');
            redirect(role_home());
        }

        if ($user['role'] === 'doctor') {
            $doctor = Doctor::findByUserId((int) $user['id']);
            if (!Patient::inDoctorCare((int) $doctor['id'], (int) $request['patient_id'])) {
                flash('error', 'You can only download results for patients under your care.');
                redirect('/doctor/patients');
            }
        }

        if ($user['role'] === 'patient') {
            $patient = Patient::findByUserId((int) $user['id']);
            if ($patient === false || (int) $patient['id'] !== (int) $request['patient_id']) {
                flash('error', 'You can only download your own results.');
                redirect('/patient/medical');
            }
        }

        $path = APP_ROOT . '/storage/lab/' . basename($request['result_file']);
        if (!is_file($path)) {
            http_response_code(404);
            echo 'Result file not found.';
            exit;
        }

        $mime = ['pdf' => 'application/pdf', 'png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg'];
        $ext  = strtolower(pathinfo($request['result_file'], PATHINFO_EXTENSION));

        header('Content-Type: ' . ($mime[$ext] ?? 'application/octet-stream'));
        header('Content-Disposition: attachment; filename="' . $request['result_file'] . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }
}