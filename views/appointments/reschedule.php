<div class="row">
    <h1 class="page-title grow">Reschedule appointment</h1>
    <a class="btn" href="<?= url($role === 'doctor' ? '/doctor/appointments' : '/receptionist/appointments') ?>">&larr; Back</a>
</div>

<div class="card mt">
    <p>
        <strong><?= e($app['patient_name']) ?></strong>
        with <strong>Dr. <?= e($doctor['name']) ?></strong>
        — currently <span class="badge badge-<?= e($app['status']) ?>"><?= e($app['status']) ?></span>
        on <?= e(format_date($app['appointment_date'])) ?> at <?= format_time($app['start_time']) ?>.
    </p>

    <form method="post" action="<?= url($role . '/appointments/' . (int) $app['id'] . '/reschedule') ?>" id="bookingForm">
        <?= csrf_field() ?>

        <div class="form-grid">
            <div class="form-group">
                <label for="doctor">Doctor</label>
                <select id="doctor" disabled>
                    <option value="<?= (int) $doctor['id'] ?>" selected>Dr. <?= e($doctor['name']) ?></option>
                </select>
            </div>
            <div class="form-group">
                <label for="date">New date</label>
                <input type="date" id="date" name="date" min="<?= date('Y-m-d') ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label>Available time slots</label>
            <div id="slotList" class="slot-list">
                <p class="text-muted">Pick a date to see available slots.</p>
            </div>
            <input type="hidden" name="start_time" id="start_time" value="">
        </div>

        <div class="row">
            <button class="btn btn-primary" type="submit" id="submitBtn" disabled>Confirm reschedule</button>
        </div>
    </form>
</div>