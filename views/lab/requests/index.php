<div class="row">
    <h1 class="page-title grow">Laboratory requests</h1>
</div>

<div class="stat-grid mt">
    <?= render_partial('partials/stat-card', ['label' => 'Requested', 'value' => (int) $counts['requested'], 'tone' => 'warn']) ?>
    <?= render_partial('partials/stat-card', ['label' => 'In progress', 'value' => (int) $counts['in_progress'], 'tone' => 'teal']) ?>
    <?= render_partial('partials/stat-card', ['label' => 'Completed', 'value' => (int) $counts['completed'], 'tone' => 'ok']) ?>
</div>

<?php if (!$requests): ?>
    <div class="card mt"><p class="muted">No lab requests yet.</p></div>
<?php else: ?>
<div class="table-wrap mt">
    <table class="table">
        <thead>
            <tr>
                <th>Requested</th>
                <th>Patient</th>
                <th>Test</th>
                <th>Priority</th>
                <th>From doctor</th>
                <th>Status</th>
                <th class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($requests as $r): ?>
            <tr>
                <td class="text-muted"><?= e(format_date($r['requested_at'])) ?></td>
                <td><?= e($r['patient_name']) ?></td>
                <td><strong><?= e($r['test_name']) ?></strong></td>
                <td>
                    <?= $r['priority'] === 'urgent'
                        ? '<span class="badge badge-rejected">Urgent</span>'
                        : '<span class="badge badge-completed">Normal</span>' ?>
                </td>
                <td><?= e($r['doctor_name']) ?></td>
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
<?php endif; ?>