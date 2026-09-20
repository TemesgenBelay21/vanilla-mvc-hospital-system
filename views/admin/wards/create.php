<div class="row">
    <h1 class="page-title grow">New ward</h1>
    <a class="btn" href="<?= url('/admin/wards') ?>">&larr; Back to wards</a>
</div>

<div class="card mt">
    <form method="post" action="<?= url('/admin/wards/store') ?>">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="name">Ward name</label>
            <input type="text" id="name" name="name" value="<?= e(old('name')) ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Description <span class="optional">(optional)</span></label>
            <textarea id="description" name="description" rows="3"><?= e(old('description')) ?></textarea>
        </div>
        <div class="row">
            <button class="btn btn-ok btn-primary" type="submit">Create ward</button>
            <a class="btn" href="<?= url('/admin/wards') ?>">Cancel</a>
        </div>
    </form>
</div>