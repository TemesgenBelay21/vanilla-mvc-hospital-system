<?php

/**
 * AvailabilityController — doctors manage their own weekly availability.
 */

declare(strict_types=1);

class AvailabilityController
{
    public function index(): void
    {
        $user   = require_role('doctor');
        $doctor = Doctor::findByUserId((int) $user['id']);

        $slots = Availability::allForDoctor((int) $doctor['id']);
        $byDay = [];
        foreach ($slots as $slot) {
            $byDay[(int) $slot['day_of_week']][] = $slot;
        }

        view('doctor/availability/index', [
            'doctor' => $doctor,
            'byDay'  => $byDay,
            'days'   => [0, 1, 2, 3, 4, 5, 6],
            'times'  => Availability::allowedTimes(),
        ]);
    }

    public function store(): void
    {
        $user   = require_role('doctor');
        csrf_check();
        $doctor = Doctor::findByUserId((int) $user['id']);

        $day   = (int) ($_POST['day_of_week'] ?? -1);
        $time  = trim($_POST['start_time'] ?? '');

        if (!in_array($day, [0, 1, 2, 3, 4, 5, 6], true)) {
            flash('error', 'Please choose a day of the week.');
            redirect('/doctor/availability');
        }
        if (!in_array($time, Availability::allowedTimes(), true)) {
            flash('error', 'Please choose a valid slot time.');
            redirect('/doctor/availability');
        }
        if (Availability::slotExists((int) $doctor['id'], $day, $time)) {
            flash('error', 'That slot already exists.');
            redirect('/doctor/availability');
        }

        Availability::create((int) $doctor['id'], $day, $time);
        flash('success', 'Slot added for ' . WEEKDAYS[$day] . ' at ' . format_time($time) . '.');
        redirect('/doctor/availability');
    }

    public function destroy(array $params): void
    {
        $user   = require_role('doctor');
        csrf_check();
        $doctor = Doctor::findByUserId((int) $user['id']);

        if (!Availability::delete((int) $params['id'], (int) $doctor['id'])) {
            flash('error', 'That slot could not be removed.');
        } else {
            flash('success', 'Slot removed.');
        }
        redirect('/doctor/availability');
    }
}