<?php

/**
 * AdmissionController — admissions, discharge and vitals, used by Admin
 * and Nurses (nurses are scoped to their assigned wards).
 */

declare(strict_types=1);

class AdmissionController
{
    /** Current admissions list — admin (all) or nurse (assigned wards). */
    public function index(): void
    {
        $user = require_role('admin', 'nurse');

        $wardIds = null;
        if ($user['role'] === 'nurse') {
            $nurse = Nurse::findByUserId((int) $user['id']);
            $wardIds = Nurse::wardIdsFor((int) $nurse['id']);
            if (count($wardIds) === 0) {
                flash('error', 'You have no wards assigned. Ask an administrator to assign you to a ward.');
                redirect('/nurse');
            }
        }

        view('admin/admissions/index', [
            'admissions' => Admission::current($wardIds),
            'isAdmin'    => $user['role'] === 'admin',
        ]);
    }

    public function create(): void
    {
        $user = require_role('admin', 'nurse');

        $wardIds = null;
        if ($user['role'] === 'nurse') {
            $nurse = Nurse::findByUserId((int) $user['id']);
            $wardIds = Nurse::wardIdsFor((int) $nurse['id']);
            if (count($wardIds) === 0) {
                flash('error', 'You have no wards assigned. Ask an administrator to assign you to a ward.');
                redirect('/nurse');
            }
        }

        $wards = Ward::all();

        $bedsByWard = [];
        foreach ($wards as $ward) {
            if ($wardIds !== null && !in_array((int) $ward['id'], $wardIds, true)) {
                continue;
            }
            $bedsByWard[(int) $ward['id']] = [
                'name' => $ward['name'],
                'beds' => Bed::freeBedsOf((int) $ward['id']),
            ];
        }

        view('admin/admissions/create', [
            'patients'   => Patient::search(''),
            'doctors'    => Doctor::allActive(),
            'bedsByWard' => $bedsByWard,
            'isAdmin'    => $user['role'] === 'admin',
        ]);
    }

    public function store(): void
    {
        $user = require_role('admin', 'nurse');
        csrf_check();

        $patientId = (int) ($_POST['patient_id'] ?? 0);
        $bedId     = (int) ($_POST['bed_id'] ?? 0);
        $doctorId  = (int) ($_POST['admitting_doctor_id'] ?? 0) ?: null;
        $reason    = trim($_POST['reason'] ?? '') ?: null;

        $bed = Bed::findById($bedId);
        if ($bed === false) {
            flash('error', 'Please choose a bed.');
            redirect($user['role'] . '/admissions/new');
        }
        $wardId = (int) $bed['ward_id'];

        if ($user['role'] === 'nurse') {
            $nurse = Nurse::findByUserId((int) $user['id']);
            if (!in_array($wardId, Nurse::wardIdsFor((int) $nurse['id']), true)) {
                flash('error', 'You can only admit patients to your assigned wards.');
                redirect('/nurse/admissions/new');
            }
        }

        $patient = Patient::findById($patientId);
        if ($patient === false) {
            flash('error', 'Please choose a patient.');
            redirect($user['role'] . '/admissions/new');
        }

        $result = Admission::admit($patientId, $wardId, $bedId, $doctorId, $reason);

        if (is_string($result)) {
            flash('error', $result);
            redirect($user['role'] . '/admissions/new');
        }

        $admission = Admission::findById((int) $result);
        if ($admission !== false) {
            notify_and_mail((int) $admission['patient_user_id'], 'You have been admitted',
                $patient['name'] . ' has been admitted to ' . $admission['ward_name'] . ' (bed ' . $admission['bed_number'] . ').',
                '/patient/medical', 'patient-admitted', [
                    'patient_name'   => $patient['name'],
                    'ward'           => $admission['ward_name'],
                    'bed'            => $admission['bed_number'],
                    'doctor_name'    => $admission['doctor_name'] ?? 'on-call physician',
                    'admission_date' => $admission['admission_date'],
                ]);
        }

        flash('success', 'Patient "' . e($patient['name']) . '" admitted. Bed number ' . e($bed['bed_number']) . '.');
        redirect('/' . $user['role'] . '/admissions/' . $result);
    }

    public function show(array $params): void
    {
        $user = require_role('admin', 'nurse');
        $admission = Admission::findById((int) $params['id']);

        if ($admission === false) {
            flash('error', 'Admission not found.');
            redirect('/' . $user['role'] . '/admissions');
        }

        if ($user['role'] === 'nurse') {
            $nurse = Nurse::findByUserId((int) $user['id']);
            if (!in_array((int) $admission['ward_id'], Nurse::wardIdsFor((int) $nurse['id']), true)) {
                flash('error', 'You can only view admissions on your assigned wards.');
                redirect('/nurse/admissions');
            }
        }

        view('admin/admissions/show', [
            'admission' => $admission,
            'vitals'    => Vitals::forAdmission((int) $admission['id']),
            'isAdmin'   => $user['role'] === 'admin',
        ]);
    }

    public function vitalsStore(array $params): void
    {
        $user = require_role('admin', 'nurse');
        csrf_check();

        $admission = Admission::findById((int) $params['id']);
        if ($admission === false) {
            flash('error', 'Admission not found.');
            redirect('/' . $user['role'] . '/admissions');
        }

        if ($admission['status'] !== 'admitted') {
            flash('error', 'Vitals can only be recorded for an active admission.');
            redirect('/' . $user['role'] . '/admissions/' . (int) $admission['id']);
        }

        Vitals::create((int) $admission['id'], (int) $user['id'], [
            'temperature' => trim($_POST['temperature'] ?? ''),
            'systolic'    => trim($_POST['systolic'] ?? ''),
            'diastolic'   => trim($_POST['diastolic'] ?? ''),
            'heart_rate'  => trim($_POST['heart_rate'] ?? ''),
            'notes'       => trim($_POST['notes'] ?? ''),
        ]);

        flash('success', 'Vitals recorded.');
        redirect('/' . $user['role'] . '/admissions/' . (int) $admission['id']);
    }

    public function discharge(array $params): void
    {
        $user = require_role('admin', 'nurse');
        csrf_check();

        $admission = Admission::findById((int) $params['id']);
        if ($admission === false) {
            flash('error', 'Admission not found.');
            redirect('/' . $user['role'] . '/admissions');
        }

        $notes = trim($_POST['discharge_notes'] ?? '') ?: null;
        $result = Admission::discharge((int) $admission['id'], $notes);

        if (is_string($result)) {
            flash('error', $result);
            redirect('/' . $user['role'] . '/admissions/' . (int) $admission['id']);
        }

        // Auto-generate the invoice for all outstanding services + ward days.
        $invoice   = BillingService::createInvoiceForPatient((int) $admission['patient_id'], (int) $user['id'], 'Auto-generated on discharge');
        $generated = $invoice['ok'] && $invoice['invoice_id'] ? (Invoice::findById((int) $invoice['invoice_id']) ?: false) : false;
        $invoiceNumber = $generated !== false ? $generated['invoice_number'] : null;

        notify_and_mail((int) $admission['patient_user_id'], 'Discharge complete',
            $admission['patient_name'] . ' has been discharged from ' . $admission['ward_name'] . '.'
            . ($generated !== false ? ' An invoice ' . $generated['invoice_number'] . ' was generated for ' . number_format((float) $generated['total_amount'], 2) . ' ETB.' : ''),
            '/patient/invoices', 'patient-discharged', [
                'patient_name'   => $admission['patient_name'],
                'discharge_date' => date('Y-m-d H:i:s'),
                'invoice_number' => $invoiceNumber,
                'total_amount'   => $generated !== false ? number_format((float) $generated['total_amount'], 2) : null,
            ]);

        flash('success', 'Patient "' . e($admission['patient_name']) . '" discharged.'
            . ($generated !== false ? ' Invoice ' . $generated['invoice_number'] . ' generated.' : ($invoice['message'] ? ' ' . $invoice['message'] : '')));
        redirect('/' . $user['role'] . '/admissions/' . (int) $admission['id']);
    }
}