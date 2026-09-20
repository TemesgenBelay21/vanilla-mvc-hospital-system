<div class="row">
    <h1 class="page-title grow">Edit <?= e($user['role']) ?></h1>
    <a class="btn" href="<?= url('/admin/staff') ?>">&larr; Back to staff</a>
</div>

<form method="post" action="<?= url('/admin/staff/' . (int) $user['id'] . '/toggle-status') ?>" class="row mt">
    <?= csrf_field() ?>
    <span class="grow">
        <?php if ($user['photo']): ?>
            <img class="mini-avatar mini-avatar-lg" src="<?= url($user['photo']) ?>" alt="Profile photo">
        <?php endif; ?>
        <span class="badge badge-<?= e($user['status']) ?>"><?= e($user['status']) ?></span>
    </span>
    <button class="btn btn-sm btn-<?= $user['status'] === 'active' ? 'danger' : 'ok' ?>" type="submit">
        <?= $user['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
    </button>
</form>

<div class="card mt">
    <form method="post" action="<?= url('/admin/staff/' . (int) $user['id'] . '/update') ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
            <div class="form-group">
                <label for="name">Full name</label>
                <input type="text" id="name" name="name" value="<?= e($user['name']) ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email" value="<?= e($user['email']) ?>" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="tel" id="phone" name="phone" value="<?= e($user['phone']) ?>">
            </div>

            <?php if ($user['role'] === 'doctor' && $doctor): ?>
                <div class="form-group">
                    <label for="department_id">Assigned department</label>
                    <select id="department_id" name="department_id" required>
                        <?php foreach ($departments as $d): ?>
                            <option value="<?= (int) $d['id'] ?>" <?= (int) $doctor['department_id'] === (int) $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="specialization">Specialization</label>
                    <input type="text" id="specialization" name="specialization" value="<?= e($doctor['specialization']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="qualification">Qualification</label>
                    <input type="text" id="qualification" name="qualification" value="<?= e($doctor['qualification']) ?>">
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="photo"><?= $user['role'] === 'doctor' ? 'Profile photo' : 'Photo' ?> <span class="optional">(optional, up to 2 MB)</span></label>
                <input type="file" id="photo" name="photo" accept="image/png,image/jpeg,image/gif,image/webp">
                <p class="text-muted" style="font-size:12.5px">Leave empty to keep the current photo.</p>
            </div>
        </div>

        <div class="row">
            <button class="btn btn-primary" type="submit">Save changes</button>
            <a class="btn" href="<?= url('/admin/staff') ?>">Cancel</a>
        </div>
    </form>
</div>

<div class="card mt">
    <h2 class="card-title">Reset password</h2>
    <form method="post" action="<?= url('/admin/staff/' . (int) $user['id'] . '/reset-password') ?>" style="max-width:420px">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="password">New password</label>
            <input type="password" id="password" name="password" required minlength="8">
        </div>
        <div class="form-group">
            <label for="password_confirmation">Confirm new password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8">
        </div>
        <button class="btn btn-warn" type="submit">Reset password</button>
    </form>
</div>