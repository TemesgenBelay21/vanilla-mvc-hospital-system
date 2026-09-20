<?php

/**
 * DepartmentController — admin CRUD for departments.
 */

declare(strict_types=1);

class DepartmentController
{
    public function index(): void
    {
        require_role('admin');
        view('admin/departments/index', [
            'departments' => Department::all(),
        ]);
    }

    public function create(): void
    {
        require_role('admin');
        view('admin/departments/create');
    }

    public function store(): void
    {
        require_role('admin');
        csrf_check();

        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (mb_strlen($name) < 2) {
            flash('error', 'Department name must be at least 2 characters.');
            keep_old(['name' => $name, 'description' => $description]);
            redirect('/admin/departments/create');
        }
        if (Department::findByName($name)) {
            flash('error', 'A department with that name already exists.');
            keep_old(['name' => $name, 'description' => $description]);
            redirect('/admin/departments/create');
        }

        Department::create($name, $description ?: null);
        flash('success', 'Department "' . $name . '" created.');
        redirect('/admin/departments');
    }

    public function edit(array $params): void
    {
        require_role('admin');
        $department = Department::findById((int) $params['id']);
        if ($department === false) {
            flash('error', 'Department not found.');
            redirect('/admin/departments');
        }
        view('admin/departments/edit', ['department' => $department]);
    }

    public function update(array $params): void
    {
        require_role('admin');
        csrf_check();

        $id          = (int) $params['id'];
        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (mb_strlen($name) < 2) {
            flash('error', 'Department name must be at least 2 characters.');
            redirect('/admin/departments/' . $id . '/edit');
        }

        Department::update($id, $name, $description ?: null);
        flash('success', 'Department updated.');
        redirect('/admin/departments');
    }

    public function destroy(array $params): void
    {
        require_role('admin');
        csrf_check();

        $id = (int) $params['id'];
        if (Department::doctorCount($id) > 0) {
            flash('error', 'This department still has doctors assigned and cannot be deleted.');
            redirect('/admin/departments');
        }

        Department::delete($id);
        flash('success', 'Department deleted.');
        redirect('/admin/departments');
    }
}