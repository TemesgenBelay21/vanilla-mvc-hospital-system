<div class="row">
    <h1 class="page-title grow">Book an appointment</h1>
    <a class="btn" href="<?= url('/receptionist/appointments') ?>">&larr; Back</a>
</div>

<div class="card mt">
    <form method="post" action="<?= url('/receptionist/appointments/book') ?>" id="bookingForm">
        <?= csrf_field() ?>

        <?php if (empty($patients)): ?>
            <p class="alert alert-warning">No patients registered yet. <a href="<?= url('/receptionist/patients/create') ?>">Register a patient</a> first.</p>
        <?php else: ?>
            <div class="form-group">
                <label for="patient_id">Patient</label>
                <select id="patient_id" name="patient_id" required>
                    <option value="">— Select patient —</option>
                    <?php foreach ($patients as $p): ?>
                        <option value="<?= (int) $p['id'] ?>" <?= (int) $selected === (int) $p['id'] ? 'selected' : '' ?>>
                            #<?= (int) $p['id'] ?> — <?= e($p['name']) ?> (<?= e($p['phone'] ?: 'no phone') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>

        <div class="form-grid">
            <div class="form-group">
                <label for="department">1 · Department</label>
                <select id="department" name="department_id" required>
                    <option value="">— Choose department —</option>
                    <?php foreach ($departments as $d): ?>
                        <option value="<?= (int) $d['id'] ?>"><?= e($d['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="doctor">2 · Doctor</label>
                <select id="doctor" name="doctor_id" required disabled>
                    <option value="">— Choose a department first —</option>
                </select>
            </div>

            <div class="form-group">
                <label for="date">3 · Date</label>
                <input type="date" id="date" name="date" min="<?= date('Y-m-d') ?>" required disabled>
            </div>
        </div>

        <div class="form-group">
            <label>4 · Time slot</label>
            <div id="slotList" class="slot-list">
                <p class="text-muted">Pick a department, doctor and date to see available slots.</p>
            </div>
            <input type="hidden" name="start_time" id="start_time" value="">
        </div>

        <div class="form-group">
            <label for="patient_notes">Notes <span class="optional">(optional)</span></label>
            <textarea id="patient_notes" name="patient_notes" placeholder="Reason for visit, symptoms…"></textarea>
        </div>

        <div class="row">
            <button class="btn btn-primary" type="submit" id="submitBtn" disabled>Request appointment</button>
        </div>
    </form>
</div>