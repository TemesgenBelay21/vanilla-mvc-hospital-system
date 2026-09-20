<div class="row">
    <h1 class="page-title grow">My appointments</h1>
    <a class="btn" href="<?= url('/doctor/availability') ?>">Manage availability</a>
</div>

<?php if ($byDate): ?>
    <?php foreach ($byDate as $date => $rows): ?>
        <div class="card mt">
            <h2 class="card-title"><?= e(format_date($date)) ?>
                <span class="text-muted" style="font-weight:400;font-size:13px">— <?= count($rows) ?> appointment<?= count($rows) > 1 ? 's' : '' ?></span>
            </h2>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Patient</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Notes</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $a): ?>
                            <tr>
                                <td><strong><?= format_time($a['start_time']) ?></strong> – <?= format_time($a['end_time']) ?></td>
                                <td><?= e($a['patient_name']) ?></td>
                                <td><?= e($a['department_name'] ?? '—') ?></td>
                                <td><span class="badge badge-<?= e($a['status']) ?>"><?= e($a['status']) ?></span></td>
                                <td style="white-space:normal;max-width:220px"><?= e($a['patient_notes'] ?: '—') ?></td>
                                <td class="text-right">
                                    <?php if (in_array($a['status'], ['pending', 'rescheduled'], true)): ?>
                                        <form method="post" action="<?= url('/doctor/appointments/' . (int) $a['id'] . '/approve') ?>" style="display:inline">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-sm btn-ok" type="submit">Approve</button>
                                        </form>
                                        <form method="post" action="<?= url('/doctor/appointments/' . (int) $a['id'] . '/reject') ?>" style="display:inline" data-confirm="Reject this appointment?">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-sm btn-danger" type="submit">Reject</button>
                                        </form>
                                        <a class="btn btn-sm" href="<?= url('/doctor/appointments/' . (int) $a['id'] . '/reschedule') ?>">Reschedule</a>
                                    <?php elseif ($a['status'] === 'approved'): ?>
                                        <form method="post" action="<?= url('/doctor/appointments/' . (int) $a['id'] . '/complete') ?>" style="display:inline">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-sm btn-ok" type="submit">Mark complete</button>
                                        </form>
                                        <a class="btn btn-sm" href="<?= url('/doctor/appointments/' . (int) $a['id'] . '/reschedule') ?>">Reschedule</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="card mt"><p class="empty-state">No upcoming appointments.</p></div>
<?php endif; ?>