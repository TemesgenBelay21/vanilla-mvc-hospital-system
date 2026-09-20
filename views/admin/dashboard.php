<h1 class="page-title">Admin dashboard</h1>

<div class="stat-grid">
    <?= render_partial('partials/stat-card', ['label' => 'Doctors',    'value' => $doctors,  'tone' => 'primary']) ?>
    <?= render_partial('partials/stat-card', ['label' => 'Receptionists', 'value' => $receptionists]) ?>
    <?= render_partial('partials/stat-card', ['label' => 'Patients',   'value' => $patients, 'tone' => 'teal']) ?>
    <?= render_partial('partials/stat-card', ['label' => 'Departments','value' => $departments]) ?>
    <?= render_partial('partials/stat-card', ['label' => 'Pending appointments', 'value' => $pending, 'tone' => 'warn']) ?>
    <?= render_partial('partials/stat-card', ['label' => "Today's appointments", 'value' => $todayAppointments, 'tone' => 'ok']) ?>
</div>

<div class="card mt">
    <h2 class="card-title">Quick actions</h2>
    <div class="quick-actions">
        <a class="btn btn-primary" href="<?= url('/admin/departments') ?>">Manage departments</a>
        <a class="btn" href="<?= url('/admin/staff') ?>">Manage staff accounts</a>
        <a class="btn" href="<?= url('/admin/patients') ?>">Browse patients</a>
    </div>
</div>