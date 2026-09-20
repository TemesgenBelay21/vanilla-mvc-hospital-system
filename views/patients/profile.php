<div class="row">
    <h1 class="page-title grow">Patient profile</h1>
    <a class="btn" href="<?= url($roleHome . '/patients') ?>">&larr; Back to patients</a>
    <?php if (!$isAdmin && !$isDoctor): ?>
        <a class="btn btn-primary" href="<?= url('/receptionist/appointments/book?patient=' . (int) $patient['id']) ?>">Book appointment</a>
    <?php endif; ?>
    <?php if ($isDoctor): ?>
        <a class="btn btn-primary" href="<?= url('/doctor/patients/' . (int) $patient['id'] . '/prescriptions/new') ?>">+ Prescribe medicine</a>
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

<div class="card mt">
    <h2 class="card-title">Admission history</h2>
    <?php if (!$admissions): ?>
        <p class="muted">No admissions on record.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Admitted</th>
                    <th>Ward / Bed</th>
                    <th>Doctor</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Discharged</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($admissions as $ad): ?>
                <tr>
                    <td class="text-muted"><?= e(format_date($ad['admission_date'])) ?></td>
                    <td><?= e($ad['ward_name']) ?> — <?= e($ad['bed_number']) ?></td>
                    <td><?= e($ad['doctor_name'] ?: '—') ?></td>
                    <td class="text-muted"><?= e($ad['admission_reason'] ?: '—') ?></td>
                    <td>
                        <?= $ad['status'] === 'admitted'
                            ? '<span class="badge badge-active">Admitted</span>'
                            : '<span class="badge badge-inactive">Discharged</span>' ?>
                    </td>
                    <td class="text-muted"><?= $ad['discharge_date'] ? e(format_date($ad['discharge_date'])) : '—' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php if ($showClinical): ?>
<div class="card mt">
    <h2 class="card-title">Prescriptions</h2>
    <?php if (!$prescriptions): ?>
        <p class="muted">No prescriptions on record.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Medicine</th>
                    <th>Dosage</th>
                    <th>Frequency</th>
                    <th>Duration</th>
                    <th>Qty</th>
                    <th>Prescribed</th>
                    <th>By</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($prescriptions as $pr): ?>
                <tr>
                    <td><strong><?= e($pr['medicine_name']) ?></strong></td>
                    <td><?= e($pr['dosage']) ?></td>
                    <td><?= e($pr['frequency']) ?></td>
                    <td><?= e($pr['duration']) ?></td>
                    <td><?= (int) $pr['quantity'] ?></td>
                    <td class="text-muted"><?= e(format_date($pr['prescribed_at'])) ?></td>
                    <td><?= e($pr['doctor_name']) ?></td>
                    <td>
                        <?= $pr['status'] === 'dispensed'
                            ? '<span class="badge badge-completed">Dispensed</span>'
                            : '<span class="badge badge-pending">Pending</span>' ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>