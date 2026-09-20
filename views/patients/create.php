<div class="row">
    <h1 class="page-title grow">Register patient</h1>
    <a class="btn" href="<?= url('/receptionist/patients') ?>">&larr; Back to patients</a>
</div>

<div class="card mt">
    <form method="post" action="<?= url('/receptionist/patients/store') ?>" autocomplete="off">
        <?= csrf_field() ?>

        <div class="form-grid">
            <div class="form-group">
                <label for="name">Full name <span class="optional">(required)</span></label>
                <input type="text" id="name" name="name" value="<?= old('name') ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email address <span class="optional">(required)</span></label>
                <input type="email" id="email" name="email" value="<?= old('email') ?>" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone number</label>
                <input type="tel" id="phone" name="phone" value="<?= old('phone') ?>">
            </div>
            <div class="form-group">
                <label for="date_of_birth">Date of birth</label>
                <input type="date" id="date_of_birth" name="date_of_birth" value="<?= old('date_of_birth') ?>">
            </div>
            <div class="form-group">
                <label for="gender">Gender</label>
                <select id="gender" name="gender">
                    <option value="">— Select —</option>
                    <?php foreach (GENDERS as $g): ?>
                        <option value="<?= e($g) ?>" <?= old('gender') === $g ? 'selected' : '' ?>><?= e(ucfirst($g)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="blood_type">Blood type</label>
                <select id="blood_type" name="blood_type">
                    <option value="">— Select —</option>
                    <?php foreach (BLOOD_TYPES as $b): ?>
                        <option value="<?= e($b) ?>" <?= old('blood_type') === $b ? 'selected' : '' ?>><?= e($b) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="emergency_contact_name">Emergency contact name</label>
                <input type="text" id="emergency_contact_name" name="emergency_contact_name" value="<?= old('emergency_contact_name') ?>">
            </div>
            <div class="form-group">
                <label for="emergency_contact_phone">Emergency contact phone</label>
                <input type="tel" id="emergency_contact_phone" name="emergency_contact_phone" value="<?= old('emergency_contact_phone') ?>">
            </div>
            <div class="form-group">
                <label for="address">Address</label>
                <input type="text" id="address" name="address" value="<?= old('address') ?>">
            </div>
            <div class="form-group">
                <label for="allergies">Known allergies</label>
                <textarea id="allergies" name="allergies" placeholder="e.g. Penicillin, peanuts"><?= old('allergies') ?></textarea>
            </div>
        </div>

        <p class="text-muted">
            The patient gets a portal account with the temporary password
            <code>Patient@123</code> (email delivery arrives in a later phase).
        </p>

        <div class="row">
            <button class="btn btn-primary" type="submit">Register patient</button>
            <a class="btn" href="<?= url('/receptionist/patients') ?>">Cancel</a>
        </div>
    </form>
</div>