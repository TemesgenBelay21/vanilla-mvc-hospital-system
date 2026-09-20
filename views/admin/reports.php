<h1 class="page-title">Reports &amp; analytics</h1>
<p class="page-sub">From <?= e(format_date($from)) ?> to <?= e(format_date($to)) ?>.</p>

<div class="card" style="margin-bottom:18px;">
    <form method="get" action="<?= url('/admin/reports') ?>" class="form-inline">
        <select name="report">
            <option value="revenue" <?= $type === 'revenue' ? 'selected' : '' ?>>Revenue</option>
            <option value="patients" <?= $type === 'patients' ? 'selected' : '' ?>>Patients</option>
            <option value="doctors" <?= $type === 'doctors' ? 'selected' : '' ?>>Doctor performance</option>
            <option value="beds" <?= $type === 'beds' ? 'selected' : '' ?>>Bed occupancy</option>
        </select>
        <input type="date" name="from" value="<?= e($from) ?>">
        <input type="date" name="to" value="<?= e($to) ?>">
        <button class="btn btn-primary" type="submit">Apply</button>
    </form>
</div>

<div class="stat-grid">
    <?= render_partial('partials/stat-card', ['label' => 'Revenue collected', 'value' => number_format($kpis['revenue'], 2) . ' ETB', 'tone' => 'ok']) ?>
    <?= render_partial('partials/stat-card', ['label' => 'New patients', 'value' => $kpis['patients'], 'tone' => 'primary']) ?>
    <?= render_partial('partials/stat-card', ['label' => 'Appointments', 'value' => $kpis['appointments'], 'tone' => 'teal']) ?>
    <?= render_partial('partials/stat-card', ['label' => 'Labs completed', 'value' => $kpis['labComplete'], 'tone' => 'info']) ?>
</div>

<?php if ($type === 'revenue' && $revenue): ?>
<div class="card mt">
    <h2 class="card-title">Daily revenue (ETB)</h2>
    <div class="chart-box"><canvas id="revenueChart" height="90"></canvas></div>
    <div class="def-list" style="margin-top:16px;">
        <dt>Cash</dt>
        <dd><?= number_format($revenue['byMethod']['cash'], 2) ?> ETB</dd>
        <dt>Telebirr</dt>
        <dd><?= number_format($revenue['byMethod']['telebirr'], 2) ?> ETB</dd>
        <dt>Total</dt>
        <dd><strong><?= number_format($revenue['total'], 2) ?> ETB</strong></dd>
    </div>
</div>
<?php endif; ?>

<?php if ($type === 'patients' && $patients): ?>
<div class="card mt">
    <h2 class="card-title">Patient registrations per day</h2>
    <div class="chart-box"><canvas id="patientChart" height="90"></canvas></div>
</div>
<div class="card mt">
    <h2 class="card-title">Appointments by department (all-time)</h2>
    <div class="chart-box"><canvas id="deptChart" height="90"></canvas></div>
</div>
<?php endif; ?>

<?php if ($type === 'doctors' && $doctors): ?>
<div class="card mt">
    <h2 class="card-title">Doctor appointments (<?= e(format_date($from)) ?> – <?= e(format_date($to)) ?>)</h2>
    <div class="chart-box"><canvas id="doctorChart" height="90"></canvas></div>
</div>
<?php if ($doctors['months']['labels']): ?>
<div class="card mt">
    <h2 class="card-title">Appointments per month</h2>
    <div class="chart-box"><canvas id="monthChart" height="90"></canvas></div>
</div>
<?php endif; ?>
<?php endif; ?>

<?php if ($type === 'beds' && $beds): ?>
<div class="card mt">
    <h2 class="card-title">Bed occupancy by ward</h2>
    <div class="chart-box"><canvas id="bedChart" height="90"></canvas></div>
</div>
<?php endif; ?>

<script src="<?= url('assets/vendor/chart.umd.min.js') ?>"></script>
<script>
(function () {
    'use strict';
    var teal = '#0f766e', tealSoft = 'rgba(15,118,110,0.15)';
    var amber = '#f59e0b', amberSoft = 'rgba(245,158,11,0.18)';
    var blue = '#2563eb', blueSoft = 'rgba(37,99,235,0.18)';
    var grid = 'rgba(148,163,184,0.18)';

    function make(id, cfg) {
        var el = document.getElementById(id);
        if (!el || typeof Chart === 'undefined') return;
        new Chart(el.getContext('2d'), {
            type: cfg.type,
            data: {
                labels: cfg.labels,
                datasets: cfg.datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { labels: { color: '#334155', boxWidth: 12 } } },
                scales: {
                    x: { ticks: { color: '#64748b', maxRotation: 45, font: { size: 11 } }, grid: { color: grid } },
                    y: { ticks: { color: '#64748b', font: { size: 11 } }, grid: { color: grid } }
                }
            }
        });
    }

<?php if ($type === 'revenue' && $revenue): ?>
    make('revenueChart', {
        type: 'line',
        labels: <?= json_encode($revenue['labels']) ?>,
        datasets: [{
            label: 'Revenue (ETB)',
            data: <?= json_encode($revenue['values']) ?>,
            borderColor: teal, backgroundColor: tealSoft, fill: true, tension: 0.3, pointRadius: 2
        }]
    });
<?php endif; ?>

<?php if ($type === 'patients' && $patients): ?>
    make('patientChart', {
        type: 'bar',
        labels: <?= json_encode($patients['labels']) ?>,
        datasets: [{
            label: 'Registrations',
            data: <?= json_encode($patients['values']) ?>,
            backgroundColor: tealSoft, borderColor: teal, borderWidth: 1
        }]
    });
    make('deptChart', {
        type: 'doughnut',
        labels: <?= json_encode($patients['byDepartment']['labels']) ?>,
        datasets: [{
            data: <?= json_encode($patients['byDepartment']['values']) ?>,
            backgroundColor: [teal, blue, 'rgba(15,118,110,0.35)', amber, 'rgba(37,99,235,0.4)']
        }]
    });
<?php endif; ?>

<?php if ($type === 'doctors' && $doctors): ?>
    make('doctorChart', {
        type: 'bar',
        labels: <?= json_encode($doctors['names']) ?>,
        datasets: [
            { label: 'Total', data: <?= json_encode($doctors['total']) ?>, backgroundColor: tealSoft, borderColor: teal, borderWidth: 1 },
            { label: 'Completed', data: <?= json_encode($doctors['completed']) ?>, backgroundColor: blueSoft, borderColor: blue, borderWidth: 1 }
        ]
    });
    <?php if ($doctors['months']['labels']): ?>
    make('monthChart', {
        type: 'line',
        labels: <?= json_encode($doctors['months']['labels']) ?>,
        datasets: [{
            label: 'Appointments',
            data: <?= json_encode($doctors['months']['values']) ?>,
            borderColor: amber, backgroundColor: amberSoft, fill: true, tension: 0.3, pointRadius: 3
        }]
    });
    <?php endif; ?>
<?php endif; ?>

<?php if ($type === 'beds' && $beds): ?>
    make('bedChart', {
        type: 'bar',
        labels: <?= json_encode($beds['names']) ?>,
        datasets: [
            { label: 'Occupied', data: <?= json_encode($beds['occupied']) ?>, backgroundColor: amberSoft, borderColor: amber, borderWidth: 1 },
            { label: 'Free', data: <?= json_encode($beds['free']) ?>, backgroundColor: tealSoft, borderColor: teal, borderWidth: 1 }
        ]
    });
<?php endif; ?>
})();
</script>