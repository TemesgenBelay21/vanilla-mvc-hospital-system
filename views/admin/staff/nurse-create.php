<div class="row">
    <h1 class="page-title grow">New nurse</h1>
    <a class="btn" href="<?= url('/admin/staff') ?>">&larr; Back to staff</a>
</div>

<div class="card mt">
    <form method="post" action="<?= url('/admin/staff/nurses/store') ?>">
        <?= csrf_field() ?>

        <div class="form-grid">
            <div class="form-group">
                <label for="name">Full name</label>
                <input type="text" id="name" name="name" value="<?= old('name') ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email" value="<?= old('email') ?>" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="tel" id="phone" name="phone" value="<?= old('phone') ?>">
            </div>
            <div class="form-group">
                <label for="password">Temporary password</label>
                <input type="password" id="password" name="password" required minlength="8">
            </div>
        </div>

        <div class="form-group">
            <label>Assigned wards <span class="optional">(choose at least one)</span></label>
            <div class="check-list" id="wards">
                <?php foreach ($wards as $ward): ?>
                    <label class="check-item">
                        <input type="checkbox" name="wards[]" value="<?= (int) $ward['id'] ?>">
                        <?= e($ward['name']) ?>
                        <span class="text-muted">— <?= (int) $ward['bed_count'] - (int) $ward['occupied_count'] ?> free beds</span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="row">
            <button class="btn btn-primary" type="submit">Create nurse</button>
            <a class="btn" href="<?= url('/admin/staff') ?>">Cancel</a>
        </div>
    </form>
</div>