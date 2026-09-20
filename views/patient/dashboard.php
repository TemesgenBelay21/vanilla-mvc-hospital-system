<h1 class="page-title">Welcome, <?= e($patient['name']) ?></h1>
<p class="page-sub text-muted">Your patient portal</p>

<div class="stat-grid">
    <?= render_partial('partials/stat-card', ['label' => 'Pending appointments', 'value' => count(array_filter($appointments, static fn($a) => $a['status'] === 'pending')), 'tone' => 'warn']) ?>
    <?= render_partial('partials/stat-card', ['label' => 'Approved appointments', 'value' => count(array_filter($appointments, static fn($a) => $a['status'] === 'approved')), 'tone' => 'ok']) ?>
</div>

<div class="grid mt">
    <div class="card">
        <h2 class="card-title">Book an appointment</h2>
        <p class="muted">Choose a department and a doctor, then pick the date and time that suits you.</p>
        <div class="quick-actions">
            <a class="btn btn-primary" href="<?= url('/patient/book') ?>">Book appointment</a>
            <a class="btn" href="<?= url('/patient/appointments') ?>">My appointments</a>
        </div>
    </div>
    <div class="card">
        <h2 class="card-title">My medical records</h2>
        <p class="muted">Admissions, vitals, prescribed medicines and lab results.</p>
        <div class="quick-actions">
            <a class="btn btn-primary" href="<?= url('/patient/medical') ?>">Open records</a>
        </div>
    </div>
</div>