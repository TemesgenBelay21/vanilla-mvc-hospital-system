<div class="row">
    <h1 class="page-title grow">Patient profile</h1>
    <a class="btn" href="<?= url($roleHome . '/patients') ?>">&larr; Back to patients</a>
    <?php if (!$isAdmin): ?>
        <a class="btn btn-primary" href="<?= url('/receptionist/appointments/book?patient=' . (int) $patient['id']) ?>">Book appointment</a>
    <?php endif; ?>
</div>

<div class="stat-grid mt">
    <?= render_partial('partials/stat-card', ['label' => 'Patient ID', 'value' => '#' . (int) $patient['id'], 'tone' => 'primary']) ?>
    <?= render_partial('partials/stat-card', ['label' => 'Account status', 'value' => $patient['status'], 'tone' => $patient['status'] === 'active' ? 'ok' : 'warn']) ?>
    <?= render_partial('partials/stat-card', ['label' => 'Registered', 'value' => format_date($patient['account_created_at']), 'tone' => 'teal']) ?>
</div>

<div class="card mt">
    <h2 class="card-title">Basic information</h2>
    <dl class="def-list">
        <dt>Full name</dt><dd><?= e($patient['name']) ?></dd>
        <dt>Email</dt><dd><?= e($patient['email']) ?></dd>
        <dt>Phone</dt><dd><?= e($patient['phone'] ?: '—') ?></dd>
        <dt>Date of birth</dt><dd><?= $patient['date_of_birth'] ? e(format_date($patient['date_of_birth'])) : '—' ?></dd>
        <dt>Gender</dt><dd><?= e($patient['gender'] ? ucfirst((string) $patient['gender']) : '—') ?></dd>
        <dt>Blood type</dt><dd><span class="badge badge-approved"><?= e($patient['blood_type'] ?: '—') ?></span></dd>
        <dt>Address</dt><dd><?= e($patient['address'] ?: '—') ?></dd>
        <dt>Known allergies</dt><dd><?= e($patient['allergies'] ?: 'None') ?></dd>
        <dt>Emergency contact</dt>
        <dd>
            <?= e($patient['emergency_contact_name'] ?: '—') ?>
            <?= $patient['emergency_contact_phone'] ? ' &middot; ' . e($patient['emergency_contact_phone']) : '' ?>
        </dd>
    </dl>
</div>

<p class="text-muted">Medical history timeline arrives with prescriptions, lab and admissions in later phases.</p>