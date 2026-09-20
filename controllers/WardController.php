<?php

/**
 * WardController — ward and bed management for Admin, plus the shared
 * ward view (bed grid) used by Admin and Nurses.
 */

declare(strict_types=1);

class WardController
{
    /** Ward list — admin only. */
    public function index(): void
    {
        require_role('admin');

        view('admin/wards/index', [
            'wards' => Ward::all(),
        ]);
    }

    /** Ward list — nurse only, restricted to assigned wards. */
    public function nurseIndex(): void
    {
        $user = require_role('nurse');
        $nurse = Nurse::findByUserId((int) $user['id']);

        view('nurse/wards/index', [
            'wards' => Nurse::wardsFor((int) $nurse['id']),
        ]);
    }

    /** Ward detail / bed grid — admin (manage) or nurse (assigned only). */
    public function show(array $params): void
    {
        $user = require_role('admin', 'nurse');
        $ward = Ward::findById((int) $params['id']);

        if ($ward === false) {
            flash('error', 'Ward not found.');
            redirect(role_home() . '/wards');
        }

        if ($user['role'] === 'nurse') {
            $nurse = Nurse::findByUserId((int) $user['id']);
            if (!in_array((int) $ward['id'], Nurse::wardIdsFor((int) $nurse['id']), true)) {
                flash('error', 'You are not assigned to that ward.');
                redirect('/nurse/wards');
            }
        }

        view('admin/wards/show', [
            'ward'   => $ward,
            'beds'   => Ward::bedsOf((int) $ward['id']),
            'isAdmin'=> $user['role'] === 'admin',
        ]);
    }

    public function create(): void
    {
        require_role('admin');
        view('admin/wards/create');
    }

    public function store(): void
    {
        require_role('admin');
        csrf_check();

        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '') ?: null;

        if (mb_strlen($name) < 2) {
            flash('error', 'Ward name must be at least 2 characters.');
            redirect('/admin/wards/create');
        }
        if (Ward::findByName($name) !== false) {
            flash('error', 'A ward with that name already exists.');
            redirect('/admin/wards/create');
        }

        Ward::create($name, $desc);
        flash('success', 'Ward "' . e($name) . '" created. Add beds to it now.');
        redirect('/admin/wards');
    }

    public function edit(array $params): void
    {
        require_role('admin');
        $ward = Ward::findById((int) $params['id']);

        if ($ward === false) {
            flash('error', 'Ward not found.');
            redirect('/admin/wards');
        }

        view('admin/wards/edit', [
            'ward' => $ward,
        ]);
    }

    public function update(array $params): void
    {
        require_role('admin');
        csrf_check();

        $id   = (int) $params['id'];
        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '') ?: null;

        if (mb_strlen($name) < 2) {
            flash('error', 'Ward name must be at least 2 characters.');
            redirect('/admin/wards/' . $id . '/edit');
        }

        $exists = Ward::findByName($name);
        if ($exists !== false && (int) $exists['id'] !== $id) {
            flash('error', 'A ward with that name already exists.');
            redirect('/admin/wards/' . $id . '/edit');
        }

        Ward::update($id, $name, $desc);
        flash('success', 'Ward updated.');
        redirect('/admin/wards/' . $id);
    }

    public function destroy(array $params): void
    {
        require_role('admin');
        csrf_check();

        $id = (int) $params['id'];
        if (Ward::delete($id)) {
            flash('success', 'Ward deleted.');
        } else {
            flash('error', 'Cannot delete a ward that still has beds.');
        }
        redirect('/admin/wards');
    }

    public function bedStore(array $params): void
    {
        require_role('admin');
        csrf_check();

        $wardId    = (int) $params['id'];
        $bedNumber = trim($_POST['bed_number'] ?? '');

        if (trim($bedNumber) === '') {
            flash('error', 'A bed number is required.');
            redirect('/admin/wards/' . $wardId);
        }
        if (Bed::create($wardId, $bedNumber)) {
            flash('success', 'Bed "' . e($bedNumber) . '" added.');
        } else {
            flash('error', 'That bed number already exists in this ward.');
        }
        redirect('/admin/wards/' . $wardId);
    }

    public function bedDestroy(array $params): void
    {
        require_role('admin');
        csrf_check();

        $bed = Bed::findById((int) $params['id']);
        $wardId = $bed !== false ? (int) $bed['ward_id'] : 0;

        if ($bed !== false && Bed::delete((int) $bed['id'])) {
            flash('success', 'Bed removed.');
        } elseif ($bed !== false) {
            flash('error', 'Cannot remove an occupied bed. Discharge the patient first.');
        } else {
            flash('error', 'That bed no longer exists.');
        }
        redirect($wardId ? '/admin/wards/' . $wardId : '/admin/wards');
    }
}