<div class="row">
    <h1 class="page-title grow">Wards</h1>
    <a class="btn btn-ok" href="<?= url('/admin/wards/create') ?>">+ New ward</a>
</div>

<?php if (!$wards): ?>
    <div class="card mt"><p class="muted">No wards yet. <a href="<?= url('/admin/wards/create') ?>">Create the first ward</a>.</p></div>
<?php else: ?>
<div class="grid mt">
    <?php foreach ($wards as $ward): ?>
    <a class="card card-link" href="<?= url('/admin/wards/' . (int) $ward['id']) ?>">
        <h3 class="card-title"><?= e($ward['name']) ?></h3>
        <p class="muted"><?= e($ward['description'] ?: 'No description') ?></p>
        <div class="stat-grid">
            <div class="stat-card"><span class="stat-label">Beds</span><span class="stat-value"><?= (int) $ward['bed_count'] ?></span></div>
            <div class="stat-card"><span class="stat-label">Occupied</span><span class="stat-value <?= (int) $ward['occupied_count'] > 0 ? 'stat-warn' : '' ?>"><?= (int) $ward['occupied_count'] ?></span></div>
            <div class="stat-card"><span class="stat-label">Free</span><span class="stat-value stat-ok"><?= (int) $ward['bed_count'] - (int) $ward['occupied_count'] ?></span></div>
        </div>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>