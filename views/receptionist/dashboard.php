<h1 class="page-title">Receptionist dashboard</h1>

<div class="stat-grid">
    <?= render_partial('partials/stat-card', ['label' => 'Registered patients', 'value' => $patients, 'tone' => 'primary']) ?>
    <?= render_partial('partials/stat-card', ['label' => 'Departments',         'value' => $departments]) ?>
    <?= render_partial('partials/stat-card', ['label' => 'Pending requests',    'value' => $pending,  'tone' => 'warn']) ?>
    <?= render_partial('partials/stat-card', ['label' => 'Approved',            'value' => $approved, 'tone' => 'ok']) ?>
    <?= render_partial('partials/stat-card', ['label' => "Today's appointments", 'value' => $todayAppointments, 'tone' => 'teal']) ?>
</div>

<div class="card mt">
    <h2 class="card-title">Quick actions</h2>
    <div class="quick-actions">
        <a class="btn btn-primary" href="<?= url('/receptionist/patients/create') ?>">Register a patient</a>
        <a class="btn" href="<?= url('/receptionist/appointments/book') ?>">Book an appointment</a>
        <a class="btn" href="<?= url('/receptionist/appointments') ?>">Manage appointments</a>
    </div>
</div>