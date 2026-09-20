<div class="row">
    <h1 class="page-title grow">Admit patient</h1>
    <a class="btn" href="<?= url(($isAdmin ? '/admin' : '/nurse') . '/admissions') ?>">&larr; Back to admissions</a>
</div>

<div class="card mt">
    <form method="post" action="<?= url(($isAdmin ? '/admin' : '/nurse') . '/admissions/store') ?>">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="patient_id">Patient</label>
            <select id="patient_id" name="patient_id" required>
                <option value="">— Select patient —</option>
                <?php foreach ($patients as $p): ?>
                    <option value="<?= (int) $p['id'] ?>" <?= (int) old('patient_id') === (int) $p['id'] ? 'selected' : '' ?>>
                        <?= e($p['name']) ?> (<?= e($p['phone'] ?: $p['email']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php $hasFree = false; ?>
        <?php foreach ($bedsByWard as $wardId => $group): ?>
            <?php if (count($group['beds']) > 0) { $hasFree = true; } ?>
        <?php endforeach; ?>

        <div class="form-group">
            <label for="bed_id">Ward / free bed</label>
            <?php if ($hasFree): ?>
            <select id="bed_id" name="bed_id" required>
                <option value="">— Select ward &amp; bed —</option>
                <?php foreach ($bedsByWard as $wardId => $group): ?>
                    <?php if (!$group['beds']) continue; ?>
                    <optgroup label="<?= e($group['name']) ?>">
                        <?php foreach ($group['beds'] as $bed): ?>
                            <option value="<?= (int) $bed['id'] ?>" <?= (int) old('bed_id') === (int) $bed['id'] ? 'selected' : '' ?>>
                                Bed <?= e($bed['bed_number']) ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
            </select>
            <?php else: ?>
            <p class="muted">No free beds available. <?php if ($isAdmin): ?><a href="<?= url('/admin/wards') ?>">Add beds to a ward</a> first.<?php endif; ?></p>
            <?php endif; ?>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label for="admitting_doctor_id">Admitting doctor <span class="optional">(optional)</span></label>
                <select id="admitting_doctor_id" name="admitting_doctor_id">
                    <option value="">— Not recorded —</option>
                    <?php foreach ($doctors as $d): ?>
                        <option value="<?= (int) $d['id'] ?>" <?= (int) old('admitting_doctor_id') === (int) $d['id'] ? 'selected' : '' ?>>
                            <?= e($d['name']) ?><?= $d['specialization'] ? ' — ' . e($d['specialization']) : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="reason">Reason for admission</label>
                <textarea id="reason" name="reason" rows="2"><?= e(old('reason')) ?></textarea>
            </div>
        </div>

        <button class="btn btn-primary" type="submit">Admit patient</button>
    </form>
</div>