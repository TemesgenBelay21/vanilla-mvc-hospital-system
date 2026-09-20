<?php

/**
 * PrescriptionController — doctors create prescriptions against
 * pharmacy inventory for patients in their care.
 */

declare(strict_types=1);

class PrescriptionController
{
    public function create(array $params): void
    {
        $user   = require_role('doctor');
        $doctor = Doctor::findByUserId((int) $user['id']);
        $patient = Patient::findById((int) $params['id']);

        if ($patient === false) {
            flash('error', 'Patient not found.');
            redirect('/doctor/patients');
        }
        if (!Patient::inDoctorCare((int) $doctor['id'], (int) $patient['id'])) {
            flash('error', 'You can only prescribe for patients under your care.');
            redirect('/doctor/patients');
        }

        view('prescriptions/create', [
            'patient'   => $patient,
            'medicines' => Medicine::all(),
        ]);
    }

    public function store(array $params): void
    {
        $user = require_role('doctor');
        csrf_check();

        $doctor  = Doctor::findByUserId((int) $user['id']);
        $patient = Patient::findById((int) $params['id']);

        if ($patient === false) {
            flash('error', 'Patient not found.');
            redirect('/doctor/patients');
        }

        $medicineId = (int) ($_POST['medicine_id'] ?? 0);
        $dosage     = trim($_POST['dosage'] ?? '');
        $frequency  = trim($_POST['frequency'] ?? '');
        $duration   = trim($_POST['duration'] ?? '');
        $quantity   = (int) ($_POST['quantity'] ?? 0);
        $notes      = trim($_POST['notes'] ?? '') ?: null;

        $medicine = Medicine::findById($medicineId);
        $errors   = [];

        if (!Patient::inDoctorCare((int) $doctor['id'], (int) $patient['id'])) {
            flash('error', 'You can only prescribe for patients under your care.');
            redirect('/doctor/patients');
        }
        if ($medicine === false) {
            $errors[] = 'Please choose a medicine from the inventory.';
        }
        if ($dosage === '') {
            $errors[] = 'A dosage is required (e.g. 500mg).';
        }
        if ($frequency === '') {
            $errors[] = 'A frequency is required (e.g. twice daily).';
        }
        if ($duration === '') {
            $errors[] = 'A duration is required (e.g. 5 days).';
        }
        if ($quantity < 1) {
            $errors[] = 'Quantity must be at least 1.';
        }

        if ($errors) {
            foreach ($errors as $error) {
                flash('error', $error);
            }
            keep_old([
                'medicine_id' => (string) $medicineId,
                'dosage'      => $dosage,
                'frequency'   => $frequency,
                'duration'    => $duration,
                'quantity'    => (string) $quantity,
                'notes'       => $notes,
            ]);
            redirect('/doctor/patients/' . (int) $patient['id'] . '/prescriptions/new');
        }

        Prescription::create(
            (int) $patient['id'],
            (int) $doctor['id'],
            $medicineId,
            $dosage,
            $frequency,
            $duration,
            $quantity,
            $notes
        );

        flash('success', 'Prescription recorded for ' . e($patient['name']) . '. The pharmacy can now dispense it.');
        redirect('/doctor/patients/' . (int) $patient['id'] . '/profile');
    }
}