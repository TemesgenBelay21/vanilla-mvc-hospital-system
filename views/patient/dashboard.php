<h1 class="page-title">Welcome, <?= e($patient['name']) ?></h1>
<p class="page-sub text-muted">Your patient portal</p>

<div class="card mt">
    <h2 class="card-title">Book an appointment</h2>
    <p>Choose a department and a doctor, then pick the date and time that suits you.</p>
    <div class="quick-actions">
        <a class="btn btn-primary" href="<?= url('/patient/book') ?>">Book appointment</a>
        <a class="btn" href="<?= url('/patient/appointments') ?>">My appointments</a>
    </div>
</div>