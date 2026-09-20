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
            'patient' => $patient,
        ]);
    }
}