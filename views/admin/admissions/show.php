<div class="row">
    <h1 class="page-title grow">
        Admission #<?= (int) $admission['id'] ?>
        <?= $admission['status'] === 'admitted' ? '<span class="badge badge-active">Active</span>' : '<span class="badge badge-inactive">Discharged</span>' ?>
    </h1>
    <a class="btn" href="<?= url(($isAdmin ? '/admin' : '/nurse') . '/admissions') ?>">&larr; Back to admissions</a>
</div>

<div class="grid mt">
    <div class="card">
        <h3 class="card-title">Patient</h3>
        <strong><?= e($admission['patient_name']) ?></strong>
        <p class="muted">
            <?= e($admission['phone']) ?><br>
            <?= $admission['date_of_birth'] ? 'DOB ' . e(date('M j, Y', strtotime($admission['date_of_birth']))) : 'No DOB' ?>
            <?= $admission['gender'] ? ' — ' . e($admission['gender']) : '' ?>
            <?= $admission['blood_type'] ? ' — Blood ' . e($admission['blood_type']) : '' ?>
        </p>
    </div>
    <div class="card">
        <h3 class="card-title">Admission</h3>
        <p>
            <span class="badge badge-active"><?= e($admission['ward_name']) ?> — <?= e($admission['bed_number']) ?></span><br>
            <span class="muted">Admitted <?= e(date('M j, Y H:i', strtotime($admission['admission_date']))) ?></span><br>
            <span class="muted">Doctor: <?= e($admission['doctor_name'] ?: 'Not recorded') ?></span>
        </p>
        <?php if ($admission['admission_reason']): ?>
            <p class="muted"><strong>Reason:</strong> <?= e($admission['admission_reason']) ?></p>
        <?php endif; ?>
        <?php if ($admission['status'] === 'discharged'): ?>
            <p class="muted">
                <strong>Discharged</strong> <?= e(date('M j, Y H:i', strtotime($admission['discharge_date']))) ?>
                <?= $admission['discharge_notes'] ? '<br>Notes: ' . e($admission['discharge_notes']) : '' ?>
            </p>
        <?php endif; ?>
    </div>
</div>

<div class="grid mt">
    <div class="card">
        <h3 class="card-title">Vitals history</h3>
        <?php if (!$vitals): ?>
            <p class="muted">No vitals recorded yet.</p>
        <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr><th>Time</th><th>Temp&nbsp;°C</th><th>BP / mmHg</th><th>HR / bpm</th><th>Notes</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($vitals as $v): ?>
                    <tr>
                        <td class="text-muted"><?= e(date('M j, Y H:i', strtotime($v['recorded_at']))) ?></td>
                        <td><?= $v['temperature'] !== null ? number_format((float) $v['temperature'], 1) : '—' ?></td>
                        <td><?= $v['systolic'] !== null ? (int) $v['systolic'] . '/' . (int) $v['diastolic'] : '—' ?></td>
                        <td><?= $v['heart_rate'] !== null ? (int) $v['heart_rate'] : '—' ?></td>
                        <td class="text-muted"><?= e($v['notes']) ?: '—' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($admission['status'] === 'admitted'): ?>
    <div class="card">
        <h3 class="card-title">Record vitals</h3>
        <form method="post" action="<?= url(($isAdmin ? '/admin' : '/nurse') . '/admissions/' . (int) $admission['id'] . '/vitals/store') ?>">
            <?= csrf_field() ?>
            <div class="form-grid">
                <div class="form-group">
                    <label for="temperature">Temperature °C</label>
                    <input type="number" id="temperature" name="temperature" step="0.1" min="30" max="45" placeholder="37.0">
                </div>
                <div class="form-group">
                    <label for="heart_rate">Heart rate / bpm</label>
                    <input type="number" id="heart_rate" name="heart_rate" min="20" max="300" placeholder="72">
                </div>
                <div class="form-group">
                    <label for="systolic">Systolic BP</label>
                    <input type="number" id="systolic" name="systolic" min="50" max="280" placeholder="120">
                </div>
                <div class="form-group">
                    <label for="diastolic">Diastolic BP</label>
                    <input type="number" id="diastolic" name="diastolic" min="30" max="200" placeholder="80">
                </div>
            </div>
            <div class="form-group">
                <label for="vnotes">Notes</label>
                <textarea id="vnotes" name="notes" rows="2" placeholder="Optional observations"></textarea>
            </div>
            <button class="btn btn-primary" type="submit">Save vitals</button>
        </form>
    </div>
    <?php endif; ?>
</div>

<?php if ($admission['status'] === 'admitted'): ?>
<div class="card mt">
    <h3 class="card-title">Discharge patient</h3>
    <form method="post" action="<?= url(($isAdmin ? '/admin' : '/nurse') . '/admissions/' . (int) $admission['id'] . '/discharge') ?>"
          onsubmit="return confirm('Discharge <?= e($admission['patient_name']) ?> and free bed <?= e($admission['bed_number']) ?>?')">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="discharge_notes">Discharge notes</label>
            <textarea id="discharge_notes" name="discharge_notes" rows="2" placeholder="Clinical summary, follow-up instructions"></textarea>
        </div>
        <button class="btn btn-danger" type="submit">Discharge patient</button>
    </form>
</div>
<?php endif; ?>