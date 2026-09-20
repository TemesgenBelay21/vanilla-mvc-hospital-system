<div class="row">
    <h1 class="page-title grow">My medical records</h1>
    <a class="btn" href="<?= url('/patient') ?>">&larr; Back to dashboard</a>
</div>

<div class="stat-grid mt">
    <?= render_partial('partials/stat-card', ['label' => 'Admissions', 'value' => count($admissions), 'tone' => 'primary']) ?>
    <?= render_partial('partials/stat-card', ['label' => 'Medications', 'value' => count($prescriptions), 'tone' => 'teal']) ?>
    <?= render_partial('partials/stat-card', ['label' => 'Lab tests', 'value' => count($labs), 'tone' => 'ok']) ?>
</div>

<?php $latestByAdmission = [];
foreach ($vitals as $v) {
    $latestByAdmission[(int) $v['admission_id']] = $v;
} ?>

<div class="card mt">
    <h2 class="card-title">Admissions &amp; vitals</h2>
    <?php if (!$admissions): ?>
        <p class="muted">You have no admissions on record.</p>
    <?php else: ?>
        <?php foreach ($admissions as $a): ?>
        <div class="admission-block">
            <div class="row between">
                <div>
                    <strong><?= e($a['ward_name']) ?></strong>
                    <span class="muted">(&shy; bed <?= e($a['bed_number']) ?>)</span>
                    <?php if ($a['status'] === 'admitted'): ?>
                        <span class="badge badge-active">Admitted</span>
                    <?php else: ?>
                        <span class="badge badge-completed">Discharged</span>
                    <?php endif; ?>
                </div>
                <span class="text-muted"><?= e(format_date($a['admission_date'])) ?>
                    <?= $a['discharge_date'] ? '– ' . e(format_date($a['discharge_date'])) : '' ?></span>
            </div>
            <?php if ($a['admission_reason']): ?>
                <p class="muted"><?= e($a['admission_reason']) ?></p>
            <?php endif; ?>
            <?php if (isset($latestByAdmission[(int) $a['id']])): $v = $latestByAdmission[(int) $a['id']]; ?>
                <p class="vitals-line">
                    Latest vitals (<span class="text-muted"><?= e(format_datetime($v['recorded_at'])) ?></span>):
                    Temp <strong><?= $v['temperature'] !== null ? (float) $v['temperature'] . '&deg;C' : '—' ?></strong>
                    &middot; BP <strong><?= $v['systolic'] !== null ? (int) $v['systolic'] . '/' . (int) $v['diastolic'] : '—' ?></strong>
                    &middot; HR <strong><?= $v['heart_rate'] !== null ? (int) $v['heart_rate'] . ' bpm' : '—' ?></strong>
                </p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div class="card mt">
    <h2 class="card-title">Prescribed medicines</h2>
    <?php if (!$prescriptions): ?>
        <p class="muted">No medicines have been prescribed.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Medicine</th><th>Dosage</th><th>Frequency</th><th>Duration</th><th>Qty</th><th>Prescribed</th><th>Status</th></tr>
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

<div class="card mt">
    <h2 class="card-title">Lab results</h2>
    <?php if (!$labs): ?>
        <p class="muted">No lab tests on record.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Requested</th><th>Test</th><th>Format</th><th>Status</th><th>Result</th></tr>
            </thead>
            <tbody>
                <?php foreach ($labs as $l): ?>
                <tr>
                    <td class="text-muted"><?= e(format_date($l['requested_at'])) ?></td>
                    <td><strong><?= e($l['test_name']) ?></strong></td>
                    <td>
                        <?= $l['priority'] === 'urgent'
                            ? '<span class="badge badge-rejected">Urgent</span>'
                            : '<span class="badge badge-completed">Normal</span>' ?>
                    </td>
                    <td>
                        <?= $l['status'] === 'requested' ? '<span class="badge badge-pending">Requested</span>'
                            : ($l['status'] === 'in_progress' ? '<span class="badge badge-active">In progress</span>'
                            : '<span class="badge badge-completed">Completed</span>') ?>
                    </td>
                    <td>
                        <?php if ($l['result_text']): ?>
                            <?= nl2br(e($l['result_text'])) ?>
                        <?php endif; ?>
                        <?php if ($l['result_file']): ?>
                            <a class="btn btn-sm" href="<?= url('/lab/files/' . (int) $l['id'] . '/download') ?>">&darr; Result file</a>
                        <?php endif; ?>
                        <?php if (!$l['result_text'] && !$l['result_file']): ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>