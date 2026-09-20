<div class="row">
    <h3 class="grow">
        <?= e($ward['name']) ?>
        <span class="text-muted">— <?= (int) $ward['occupied_count'] ?>/<?= (int) $ward['bed_count'] ?> beds occupied</span>
    </h3>
    <?php if ($isAdmin): ?>
        <a class="btn btn-sm" href="<?= url('/admin/wards/' . (int) $ward['id'] . '/edit') ?>">Edit ward</a>
        <a class="btn btn-sm btn-primary" href="<?= url('/admin/admissions/new') ?>">+ Admit patient</a>
    <?php else: ?>
        <a class="btn btn-sm btn-primary" href="<?= url('/nurse/admissions/new') ?>">+ Admit patient</a>
    <?php endif; ?>
</div>

<?php if ($isAdmin): ?>
<?php if (!$beds): ?>
<div class="card mt">
    <form method="post" action="<?= url('/admin/wards/' . (int) $ward['id'] . '/beds/store') ?>" class="row">
        <?= csrf_field() ?>
        <input type="text" name="bed_number" class="grow" placeholder="Bed number, e.g. G-1" required>
        <button class="btn btn-ok" type="submit">Add first bed</button>
    </form>
</div>
<?php endif; ?>
<?php endif; ?>

<?php if (!$beds): ?>
    <div class="card mt"><p class="muted">No beds in this ward yet<?= $isAdmin ? ' — use the form above to add one' : '' ?>.</p></div>
<?php else: ?>
<div class="bed-grid mt">
    <?php foreach ($beds as $bed): ?>
        <div class="bed <?= $bed['status'] === 'occupied' ? 'bed-occupied' : 'bed-free' ?>">
            <div class="bed-label"><?= e($bed['bed_number']) ?></div>
            <?php if ($bed['status'] === 'occupied'): ?>
                <div class="bed-patient">
                    <a href="<?= url(($isAdmin ? '/admin' : '/nurse') . '/admissions/' . (int) $bed['admission_id']) ?>"><?= e($bed['patient_name']) ?></a>
                </div>
            <?php else: ?>
                <div class="bed-patient text-muted">Free</div>
            <?php endif; ?>
            <?php if ($isAdmin && $bed['status'] === 'free'): ?>
                <form method="post" action="<?= url('/admin/beds/' . (int) $bed['id'] . '/delete') ?>">
                    <?= csrf_field() ?>
                    <button class="link-danger" type="submit">remove</button>
                </form>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if ($isAdmin): ?>
<div class="card mt">
    <h3 class="card-title">Add bed</h3>
    <form method="post" action="<?= url('/admin/wards/' . (int) $ward['id'] . '/beds/store') ?>" class="row">
        <?= csrf_field() ?>
        <input type="text" name="bed_number" class="grow" placeholder="Bed number, e.g. G-5" required>
        <button class="btn btn-ok" type="submit">Add bed</button>
    </form>
</div>
<?php endif; ?>