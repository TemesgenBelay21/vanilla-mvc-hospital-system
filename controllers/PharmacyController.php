<?php

/**
 * PharmacyController — inventory management (admin) and the dispensing
 * queue (pharmacist).
 */

declare(strict_types=1);

class PharmacyController
{
    /** Inventory management — admin only. */
    public function index(): void
    {
        require_role('admin');

        view('admin/medicines/index', [
            'medicines' => Medicine::all(),
        ]);
    }

    public function create(): void
    {
        require_role('admin');
        view('admin/medicines/create');
    }

    public function store(): void
    {
        require_role('admin');
        csrf_check();

        $this->applyCreateUpdate(null);
    }

    public function edit(array $params): void
    {
        require_role('admin');
        $medicine = Medicine::findById((int) $params['id']);
        if ($medicine === false) {
            flash('error', 'Medicine not found.');
            redirect('/admin/medicines');
        }
        view('admin/medicines/edit', [
            'medicine' => $medicine,
        ]);
    }

    public function update(array $params): void
    {
        require_role('admin');
        csrf_check();
        $this->applyCreateUpdate((int) $params['id']);
    }

    public function restock(array $params): void
    {
        require_role('admin');
        csrf_check();

        $amount = (int) ($_POST['amount'] ?? 0);
        $id     = (int) $params['id'];

        if (Medicine::findById($id) === false) {
            flash('error', 'Medicine not found.');
            redirect('/admin/medicines');
        }
        if ($amount < 1) {
            flash('error', 'Restock amount must be at least 1.');
            redirect('/admin/medicines');
        }

        Medicine::addStock($id, $amount);
        flash('success', 'Stock updated.');
        redirect('/admin/medicines');
    }

    public function destroy(array $params): void
    {
        require_role('admin');
        csrf_check();

        $medicine = Medicine::findById((int) $params['id']);
        if ($medicine === false) {
            flash('error', 'Medicine not found.');
            redirect('/admin/medicines');
        }

        if (Medicine::delete((int) $medicine['id'])) {
            flash('success', 'Medicine "' . e($medicine['name']) . '" deleted.');
        } else {
            flash('error', 'Cannot delete a medicine that is used by prescriptions.');
        }
        redirect('/admin/medicines');
    }

    private function applyCreateUpdate(?int $id): void
    {
        $name      = trim($_POST['name'] ?? '');
        $category  = trim($_POST['category'] ?? '') ?: null;
        $price     = (float) ($_POST['unit_price'] ?? 0);
        $expiry    = trim($_POST['expiry_date'] ?? '') ?: null;
        $supplier  = trim($_POST['supplier'] ?? '') ?: null;
        $threshold = (int) ($_POST['low_stock_threshold'] ?? 0);
        $restock   = $id === null ? (int) ($_POST['stock_quantity'] ?? 0) : 0;

        $errors = [];
        if (mb_strlen($name) < 2) {
            $errors[] = 'Medicine name must be at least 2 characters.';
        } elseif (($existing = Medicine::findByName($name)) !== false && ($id === null || (int) $existing['id'] !== $id)) {
            $errors[] = 'A medicine with that name already exists.';
        }
        if ($price < 0) {
            $errors[] = 'Unit price cannot be negative.';
        }
        if ($threshold < 0) {
            $errors[] = 'Low stock threshold cannot be negative.';
        }
        if ($id === null && $restock < 0) {
            $errors[] = 'Initial stock cannot be negative.';
        }
        if ($expiry !== null && !strtotime($expiry)) {
            $errors[] = 'Expiry date is invalid.';
        }

        if ($errors) {
            foreach ($errors as $error) {
                flash('error', $error);
            }
            keep_old([
                'name'                => $name,
                'category'            => $category,
                'unit_price'          => (string) $price,
                'expiry_date'         => $expiry,
                'supplier'            => $supplier,
                'low_stock_threshold' => (string) $threshold,
                'stock_quantity'      => (string) $restock,
            ]);
            redirect($id === null ? '/admin/medicines/create' : '/admin/medicines/' . $id . '/edit');
        }

        if ($id === null) {
            Medicine::create($name, $category, $restock, $price, $expiry, $supplier, $threshold);
            flash('success', 'Medicine "' . e($name) . '" added to inventory.');
        } else {
            Medicine::update($id, $name, $category, $price, $expiry, $supplier, $threshold);
            flash('success', 'Medicine updated.');
        }
        redirect('/admin/medicines');
    }

    /** Inventory view — pharmacist only (read-only). */
    public function pharmacistIndex(): void
    {
        require_role('pharmacist');

        view('pharmacist/medicines/index', [
            'medicines' => Medicine::all(),
        ]);
    }

    /** Dispensing queue — pharmacist only. */
    public function queue(): void
    {
        require_role('pharmacist');

        view('pharmacist/dispense/index', [
            'prescriptions' => Prescription::pendingList(),
        ]);
    }

    public function review(array $params): void
    {
        require_role('pharmacist');

        $prescription = Prescription::findById((int) $params['id']);
        if ($prescription === false || $prescription['status'] !== 'pending') {
            flash('error', 'Prescription not found or already handled.');
            redirect('/pharmacist/dispense');
        }

        view('pharmacist/dispense/show', [
            'prescription' => $prescription,
        ]);
    }

    public function dispense(array $params): void
    {
        $user = require_role('pharmacist');
        csrf_check();

        $id = (int) $params['id'];
        $result = Prescription::dispense($id, (int) $user['id']);

        if (is_string($result)) {
            flash('error', $result);
            redirect('/pharmacist/dispense/' . $id);
        }

        flash('success', 'Prescription dispensed and stock updated.');
        redirect('/pharmacist/dispense');
    }
}