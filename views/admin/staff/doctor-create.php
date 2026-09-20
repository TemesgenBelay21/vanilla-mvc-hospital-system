<div class="row">
    <h1 class="page-title grow">New doctor</h1>
    <a class="btn" href="<?= url('/admin/staff') ?>">&larr; Back to staff</a>
</div>

<div class="card mt">
    <form method="post" action="<?= url('/admin/staff/doctors/store') ?>" enctype="multipart/form-data">
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
            <div class="form-group">
                <label for="department_id">Assigned department</label>
                <select id="department_id" name="department_id" required>
                    <option value="">— Select —</option>
                    <?php foreach ($departments as $d): ?>
                        <option value="<?= (int) $d['id'] ?>" <?= old('department_id') == $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="specialization">Specialization</label>
                <input type="text" id="specialization" name="specialization" value="<?= old('specialization') ?>" required placeholder="e.g. Interventional Cardiologist">
            </div>
            <div class="form-group">
                <label for="qualification">Qualification</label>
                <input type="text" id="qualification" name="qualification" value="<?= old('qualification') ?>" placeholder="e.g. MD, AAU">
            </div>
            <div class="form-group">
                <label for="photo">Profile photo <span class="optional">(optional, JPEG/PNG up to 2 MB)</span></label>
                <input type="file" id="photo" name="photo" accept="image/png,image/jpeg,image/gif,image/webp">
            </div>
        </div>

        <div class="row">
            <button class="btn btn-primary" type="submit">Create doctor</button>
            <a class="btn" href="<?= url('/admin/staff') ?>">Cancel</a>
        </div>
    </form>
</div>