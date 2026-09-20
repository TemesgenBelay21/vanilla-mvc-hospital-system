<div class="row">
    <h1 class="page-title grow">New department</h1>
    <a class="btn" href="<?= url('/admin/departments') ?>">&larr; Back</a>
</div>

<div class="card mt">
    <form method="post" action="<?= url('/admin/departments/store') ?>">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="name">Department name</label>
            <input type="text" id="name" name="name" value="<?= old('name') ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" placeholder="What does this department handle?"><?= old('description') ?></textarea>
        </div>
        <div class="row">
            <button class="btn btn-primary" type="submit">Create department</button>
            <a class="btn" href="<?= url('/admin/departments') ?>">Cancel</a>
        </div>
    </form>
</div>