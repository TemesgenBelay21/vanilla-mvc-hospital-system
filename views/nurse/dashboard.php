<h1 class="page-title">Nurse dashboard</h1>
<p class="page-sub text-muted"><?= e($nurse['name']) ?></p>

<div class="stat-grid">
    <?= render_partial('partials/stat-card', ['label' => 'Assigned wards', 'value' => count($wards), 'tone' => 'primary']) ?>
    <?= render_partial('partials/stat-card', ['label' => 'Active admissions', 'value' => count($activeAdmits), 'tone' => 'warn']) ?>
</div>

<div class="row mt">
    <a class="btn btn-primary" href="<?= url('/nurse/admissions/new') ?>">+ New admission</a>
    <a class="btn" href="<?= url('/nurse/admissions') ?>">All admissions</a>
</div>

<?php if ($wards): ?>
    <div class="card mt">
        <h2 class="card-title">Your wards</h2>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr><th>Ward</th><th>Beds</th><th>Occupied</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($wards as $ward): ?>
                        <tr>
                            <td><?= e($ward['name']) ?></td>
                            <td><?= (int) $ward['bed_count'] ?></td>
                            <td><?= (int) $ward['occupied_count'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php else: ?>
    <div class="card mt"><p class="empty-state">You are not assigned to any wards yet.</p></div>
<?php endif; ?>

<?php if ($activeAdmits): ?>
<div class="card mt">
    <h2 class="card-title">Currently admitted (your wards)</h2>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Patient</th><th>Bed</th><th>Admitted</th><th class="text-right">Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($activeAdmits as $a): ?>
                <tr>
                    <td><?= e($a['patient_name']) ?></td>
                    <td><span class="badge badge-active"><?= e($a['bed_number']) ?></span></td>
                    <td class="text-muted"><?= e(format_date($a['admitted_at'])) ?></td>
                    <td class="text-right">
                        <a class="btn btn-sm" href="<?= url('/nurse/admissions/' . (int) $a['id']) ?>">View</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>