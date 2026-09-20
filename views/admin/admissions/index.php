<div class="row">
    <h1 class="page-title grow">Current admissions</h1>
    <a class="btn btn-primary" href="<?= url(($isAdmin ? '/admin' : '/nurse') . '/admissions/new') ?>">+ Admit patient</a>
</div>

<?php if (!$admissions): ?>
    <div class="card mt"><p class="muted">No active admissions.</p></div>
<?php else: ?>
<div class="table-wrap mt">
    <table class="table">
        <thead>
            <tr>
                <th>Patient</th>
                <th>Ward / Bed</th>
                <th>Admitting doctor</th>
                <th>Admitted</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($admissions as $a): ?>
            <tr>
                <td><?= e($a['patient_name']) ?></td>
                <td><span class="badge badge-active"><?= e($a['ward_name']) ?> — <?= e($a['bed_number']) ?></span></td>
                <td><?= e($a['doctor_name'] ?: 'Not recorded') ?></td>
                <td class="text-muted"><?= e(date('M j, Y H:i', strtotime($a['admission_date']))) ?></td>
                <td><a class="btn btn-sm" href="<?= url(($isAdmin ? '/admin' : '/nurse') . '/admissions/' . (int) $a['id']) ?>">View</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>