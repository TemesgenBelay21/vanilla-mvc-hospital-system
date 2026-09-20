<h1 class="page-title">Laboratory dashboard</h1>
<p class="page-sub text-muted">Test request queue and result processing.</p>

<div class="stat-grid">
    <?= render_partial('partials/stat-card', ['label' => 'Requested', 'value' => (int) $counts['requested'], 'tone' => 'warn']) ?>
    <?= render_partial('partials/stat-card', ['label' => 'In progress', 'value' => (int) $counts['in_progress'], 'tone' => 'teal']) ?>
    <?= render_partial('partials/stat-card', ['label' => 'Completed', 'value' => (int) $counts['completed'], 'tone' => 'ok']) ?>
</div>

<div class="row mt">
    <a class="btn btn-primary" href="<?= url('/lab/requests') ?>">Open queue</a>
</div>

<?php if ($recent): ?>
<div class="card mt">
    <h2 class="card-title">Recent requests</h2>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Requested</th><th>Patient</th><th>Test</th><th>Status</th><th class="text-right">Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($recent as $r): ?>
                <tr>
                    <td class="text-muted"><?= e(format_date($r['requested_at'])) ?></td>
                    <td><?= e($r['patient_name']) ?></td>
                    <td><strong><?= e($r['test_name']) ?></strong></td>
                    <td>
                        <?php if ($r['status'] === 'requested'): ?>
                            <span class="badge badge-pending">Requested</span>
                        <?php elseif ($r['status'] === 'in_progress'): ?>
                            <span class="badge badge-active">In progress</span>
                        <?php else: ?>
                            <span class="badge badge-completed">Completed</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-right">
                        <a class="btn btn-sm" href="<?= url('/lab/requests/' . (int) $r['id']) ?>">Open</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php else: ?>
<div class="card mt"><p class="empty-state">No lab requests yet.</p></div>
<?php endif; ?>