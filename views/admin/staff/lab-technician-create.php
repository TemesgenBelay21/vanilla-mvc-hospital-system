<div class="row">
    <h1 class="page-title grow">New lab technician</h1>
    <a class="btn" href="<?= url('/admin/staff') ?>">&larr; Back to staff</a>
</div>

<div class="card mt">
    <form method="post" action="<?= url('/admin/staff/lab-technicians/store') ?>">
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

        <div class="row">
            <button class="btn btn-primary" type="submit">Create lab technician</button>
            <a class="btn" href="<?= url('/admin/staff') ?>">Cancel</a>
        </div>
    </form>
</div>