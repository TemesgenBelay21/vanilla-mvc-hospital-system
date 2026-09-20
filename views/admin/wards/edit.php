<div class="row">
    <h1 class="page-title grow">Edit ward</h1>
    <a class="btn" href="<?= url('/admin/wards/' . (int) $ward['id']) ?>">&larr; Back to ward</a>
</div>

<div class="card mt">
    <form method="post" action="<?= url('/admin/wards/' . (int) $ward['id'] . '/update') ?>">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="name">Ward name</label>
            <input type="text" id="name" name="name" value="<?= e($ward['name']) ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3"><?= e($ward['description']) ?></textarea>
        </div>
        <button class="btn btn-primary" type="submit">Save changes</button>
    </form>

    <hr class="mt">

    <form method="post" action="<?= url('/admin/wards/' . (int) $ward['id'] . '/delete') ?>" onsubmit="return confirm('Delete this ward?')">
        <?= csrf_field() ?>
        <button class="btn btn-danger" type="submit">Delete ward</button>
    </form>
</div>