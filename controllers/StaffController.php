<?php

/**
 * StaffController — admin management of doctor and receptionist accounts.
 */

declare(strict_types=1);

class StaffController
{
    public function index(): void
    {
        require_role('admin');
        view('admin/staff/index', [
            'staff' => User::staff(),
        ]);
    }

    public function doctorCreate(): void
    {
        require_role('admin');
        view('admin/staff/doctor-create', [
            'departments' => Department::all(),
        ]);
    }

    public function receptionistCreate(): void
    {
        require_role('admin');
        view('admin/staff/receptionist-create');
    }

    public function doctorStore(): void
    {
        require_role('admin');
        csrf_check();

        $input = [
            'name'            => trim($_POST['name'] ?? ''),
            'email'           => strtolower(trim($_POST['email'] ?? '')),
            'phone'           => trim($_POST['phone'] ?? ''),
            'password'        => (string) ($_POST['password'] ?? ''),
            'specialization'  => trim($_POST['specialization'] ?? ''),
            'qualification'   => trim($_POST['qualification'] ?? ''),
            'department_id'   => (int) ($_POST['department_id'] ?? 0),
        ];

        $errors = [];
        if (mb_strlen($input['name']) < 3) {
            $errors[] = 'Full name must be at least 3 characters.';
        }
        if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid email address is required.';
        } elseif (User::findByEmail($input['email']) !== false) {
            $errors[] = 'That email address is already in use.';
        }
        if (mb_strlen($input['password']) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        }
        if ($input['specialization'] === '') {
            $errors[] = 'Specialization is required.';
        }
        if (Department::findById($input['department_id']) === false) {
            $errors[] = 'Please choose a valid department.';
        }

        if ($errors) {
            foreach ($errors as $error) {
                flash('error', $error);
            }
            keep_old(array_merge($input, ['password' => '']));
            redirect('/admin/staff/doctors/create');
        }

        $photo = handle_photo_upload($_FILES['photo'] ?? [], 'doctors');

        $userId = User::create([
            'role'     => 'doctor',
            'name'     => $input['name'],
            'email'    => $input['email'],
            'password' => $input['password'],
            'phone'    => $input['phone'] ?: null,
            'photo'    => $photo,
        ]);
        Doctor::createFor($userId, $input['department_id'], $input['specialization'], $input['qualification'] ?: null);

        flash('success', 'Doctor "' . $input['name'] . '" created.');
        redirect('/admin/staff');
    }

    public function receptionistStore(): void
    {
        require_role('admin');
        csrf_check();

        $input = [
            'name'     => trim($_POST['name'] ?? ''),
            'email'    => strtolower(trim($_POST['email'] ?? '')),
            'phone'    => trim($_POST['phone'] ?? ''),
            'password' => (string) ($_POST['password'] ?? ''),
        ];

        $errors = [];
        if (mb_strlen($input['name']) < 3) {
            $errors[] = 'Full name must be at least 3 characters.';
        }
        if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid email address is required.';
        } elseif (User::findByEmail($input['email']) !== false) {
            $errors[] = 'That email address is already in use.';
        }
        if (mb_strlen($input['password']) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        }

        if ($errors) {
            foreach ($errors as $error) {
                flash('error', $error);
            }
            keep_old(array_merge($input, ['password' => '']));
            redirect('/admin/staff/receptionists/create');
        }

        $userId = User::create([
            'role'     => 'receptionist',
            'name'     => $input['name'],
            'email'    => $input['email'],
            'password' => $input['password'],
            'phone'    => $input['phone'] ?: null,
        ]);

        flash('success', 'Receptionist "' . $input['name'] . '" created.');
        redirect('/admin/staff');
    }

    public function edit(array $params): void
    {
        require_role('admin');
        $user = User::findById((int) $params['id']);

        if ($user === false || !in_array($user['role'], ['doctor', 'receptionist'], true)) {
            flash('error', 'Staff account not found.');
            redirect('/admin/staff');
        }

        $doctor = $user['role'] === 'doctor' ? Doctor::findByUserId((int) $user['id']) : null;

        view('admin/staff/edit', [
            'user'        => $user,
            'doctor'      => $doctor,
            'departments' => Department::all(),
        ]);
    }

    public function update(array $params): void
    {
        require_role('admin');
        csrf_check();

        $id   = (int) $params['id'];
        $user = User::findById($id);
        if ($user === false) {
            flash('error', 'Staff account not found.');
            redirect('/admin/staff');
        }

        $input = [
            'name'           => trim($_POST['name'] ?? ''),
            'email'          => strtolower(trim($_POST['email'] ?? '')),
            'phone'          => trim($_POST['phone'] ?? ''),
            'specialization' => trim($_POST['specialization'] ?? ''),
            'qualification'  => trim($_POST['qualification'] ?? ''),
            'department_id'  => (int) ($_POST['department_id'] ?? 0),
        ];

        $errors = [];
        if (mb_strlen($input['name']) < 3) {
            $errors[] = 'Full name must be at least 3 characters.';
        }
        if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid email address is required.';
        } elseif (($existing = User::findByEmail($input['email'])) !== false && (int) $existing['id'] !== $id) {
            $errors[] = 'That email address is already in use.';
        }
        if ($user['role'] === 'doctor' && Department::findById($input['department_id']) === false) {
            $errors[] = 'Please choose a valid department.';
        }

        if ($errors) {
            foreach ($errors as $error) {
                flash('error', $error);
            }
            redirect('/admin/staff/' . $id . '/edit');
        }

        $photo = handle_photo_upload($_FILES['photo'] ?? [], 'doctors') ?? $user['photo'];

        User::update($id, [
            'name'  => $input['name'],
            'email' => $input['email'],
            'phone' => $input['phone'] ?: null,
            'photo' => $photo,
        ]);

        if ($user['role'] === 'doctor') {
            $doctor = Doctor::findByUserId($id);
            Doctor::updateProfile(
                (int) $doctor['id'],
                $input['department_id'],
                $input['specialization'],
                $input['qualification'] ?: null
            );
        }

        flash('success', 'Staff account updated.');
        redirect('/admin/staff');
    }

    public function toggleStatus(array $params): void
    {
        require_role('admin');
        csrf_check();

        $id   = (int) $params['id'];
        $user = User::findById($id);
        if ($user === false || !in_array($user['role'], ['doctor', 'receptionist'], true)) {
            flash('error', 'Staff account not found.');
            redirect('/admin/staff');
        }

        $newStatus = $user['status'] === 'active' ? 'inactive' : 'active';
        User::setStatus($id, $newStatus);

        flash('success', 'Account "' . $user['name'] . '" ' . ($newStatus === 'active' ? 'activated.' : 'deactivated.'));
        redirect('/admin/staff');
    }

    public function resetPassword(array $params): void
    {
        require_role('admin');
        csrf_check();

        $id   = (int) $params['id'];
        $user = User::findById($id);
        if ($user === false) {
            flash('error', 'Staff account not found.');
            redirect('/admin/staff');
        }

        $password = (string) ($_POST['password'] ?? '');
        $confirm  = (string) ($_POST['password_confirmation'] ?? '');

        if (mb_strlen($password) < 8) {
            flash('error', 'New password must be at least 8 characters.');
            redirect('/admin/staff/' . $id . '/edit');
        }
        if ($password !== $confirm) {
            flash('error', 'Passwords do not match.');
            redirect('/admin/staff/' . $id . '/edit');
        }

        User::updatePassword($id, $password);
        flash('success', 'Password updated for "' . $user['name'] . '".');
        redirect('/admin/staff');
    }
}