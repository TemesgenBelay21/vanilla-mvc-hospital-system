<div class="row">
    <h1 class="page-title grow">Edit department</h1>
    <a class="btn" href="<?= url('/admin/departments') ?>">&larr; Back</a>
</div>

<div class="card mt">
    <form method="post" action="<?= url('/admin/departments/' . (int) $department['id'] . '/update') ?>">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="name">Department name</label>
            <input type="text" id="name" name="name" value="<?= e($department['name']) ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description"><?= e($department['description']) ?></textarea>
        </div>
        <div class="row">
            <button class="btn btn-primary" type="submit">Save changes</button>
            <a class="btn" href="<?= url('/admin/departments') ?>">Cancel</a>
        </div>
    </form>
</div>