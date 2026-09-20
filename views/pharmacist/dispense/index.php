<div class="row">
    <h1 class="page-title grow">Dispensing queue</h1>
    <a class="btn" href="<?= url('/pharmacist') ?>">Dashboard</a>
</div>

<?php if (!$prescriptions): ?>
    <div class="card mt"><p class="muted">No pending prescriptions to dispense.</p></div>
<?php else: ?>
<div class="stat-grid mt">
    <?= render_partial('partials/stat-card', ['label' => 'Pending', 'value' => count($prescriptions), 'tone' => 'warn']) ?>
</div>
<div class="table-wrap mt">
    <table class="table">
        <thead>
            <tr>
                <th>Patient</th>
                <th>Medicine</th>
                <th>Dosage</th>
                <th>Frequency</th>
                <th>Qty</th>
                <th>Prescribed</th>
                <th>Doctor</th>
                <th class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($prescriptions as $pr): ?>
            <tr>
                <td><?= e($pr['patient_name']) ?></td>
                <td><strong><?= e($pr['medicine_name']) ?></strong></td>
                <td><?= e($pr['dosage']) ?></td>
                <td><?= e($pr['frequency']) ?></td>
                <td><?= (int) $pr['quantity'] ?></td>
                <td class="text-muted"><?= e(format_date($pr['prescribed_at'])) ?></td>
                <td><?= e($pr['doctor_name']) ?></td>
                <td class="text-right">
                    <a class="btn btn-sm btn-primary" href="<?= url('/pharmacist/dispense/' . (int) $pr['id']) ?>">Dispense</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>