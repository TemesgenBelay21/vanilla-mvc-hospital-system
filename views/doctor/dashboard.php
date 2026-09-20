<h1 class="page-title">
    Welcome, <?= e($doctor['name']) ?>
</h1>
<p class="page-sub text-muted">
    <?= e($doctor['specialization']) ?> · <?= e($doctor['department_name']) ?>
</p>

<div class="stat-grid">
    <?= render_partial('partials/stat-card', ['label' => "Today's appointments", 'value' => $todayAppointments, 'tone' => 'primary']) ?>
    <?= render_partial('partials/stat-card', ['label' => 'Pending requests',  'value' => $pending,   'tone' => 'warn']) ?>
    <?= render_partial('partials/stat-card', ['label' => 'Approved',          'value' => $approved,  'tone' => 'ok']) ?>
    <?= render_partial('partials/stat-card', ['label' => 'Completed',         'value' => $completed, 'tone' => 'teal']) ?>
</div>

<div class="card mt">
    <h2 class="card-title">Quick actions</h2>
    <div class="quick-actions">
        <a class="btn btn-primary" href="<?= url('/doctor/appointments') ?>">Review appointments</a>
        <a class="btn" href="<?= url('/doctor/availability') ?>">Set availability</a>
    </div>
</div>