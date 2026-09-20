<?php

/**
 * DashboardController — role-scoped homes with headline stats.
 */

declare(strict_types=1);

class DashboardController
{
    public function admin(): void
    {
        require_role('admin');

        view('admin/dashboard', [
            'doctors'          => Doctor::countOf(),
            'receptionists'    => User::countByRole('receptionist'),
            'patients'         => Patient::countOf(),
            'departments'      => Department::countOf(),
            'pending'          => Appointment::countByStatus('pending'),
            'approved'         => Appointment::countByStatus('approved'),
            'todayAppointments' => Appointment::countToday(),
        ]);
    }

    public function doctor(): void
    {
        $user   = require_role('doctor');
        $doctor = Doctor::findByUserId((int) $user['id']);

        view('doctor/dashboard', [
            'doctor'          => $doctor,
            'todayAppointments' => Appointment::countToday((int) $doctor['id']),
            'pending'         => Appointment::countByStatus('pending', (int) $doctor['id']),
            'approved'        => Appointment::countByStatus('approved', (int) $doctor['id']),
            'completed'       => Appointment::countByStatus('completed', (int) $doctor['id']),
        ]);
    }

    public function receptionist(): void
    {
        require_role('receptionist');

        view('receptionist/dashboard', [
            'patients'         => Patient::countOf(),
            'departments'      => Department::countOf(),
            'pending'          => Appointment::countByStatus('pending'),
            'approved'         => Appointment::countByStatus('approved'),
            'todayAppointments' => Appointment::countToday(),
        ]);
    }

    public function patient(): void
    {
        $user    = require_role('patient');
        $patient = Patient::findByUserId((int) $user['id']);

        view('patient/dashboard', [
            'patient'      => $patient,
            'appointments' => Appointment::forPatient((int) $patient['id']),
        ]);
    }

    public function nurse(): void
    {
        $user  = require_role('nurse');
        $nurse = Nurse::findByUserId((int) $user['id']);
        $wards = Nurse::wardsFor((int) $nurse['id']);
        $wardIds = array_column($wards, 'id');
        $active = $wardIds ? Admission::current($wardIds) : [];

        view('nurse/dashboard', [
            'nurse'         => $nurse,
            'wards'         => $wards,
            'activeAdmits'  => $active,
        ]);
    }

    public function pharmacist(): void
    {
        require_role('pharmacist');

        view('pharmacist/dashboard', [
            'medicines'    => Medicine::countOf(),
            'lowStock'     => Medicine::lowStockCount(),
            'pendingCount' => Prescription::pendingCount(),
            'lowList'      => Medicine::all(),
            'queue'        => Prescription::pendingList(),
        ]);
    }

    public function labTechnician(): void
    {
        require_role('lab_technician');

        $all = LabRequest::all();

        view('lab/dashboard', [
            'counts'    => LabRequest::counts(),
            'recent'    => array_slice($all, 0, 6),
        ]);
    }

    public function accountant(): void
    {
        require_role('accountant');

        view('accountant/dashboard', [
            'invoices'   => Invoice::countOf(),
            'paid'       => Invoice::countByStatus('paid'),
            'unpaid'     => Invoice::countByStatus('unpaid'),
            'partial'    => Invoice::countByStatus('partially_paid'),
            'outstanding'=> Invoice::outstandingTotal(),
            'monthRevenue' => Payment::sumBetween(date('Y-m-01'), date('Y-m-d')),
            'recent'     => Payment::recent(6),
        ]);
    }
}