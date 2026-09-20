<div class="row">
    <h1 class="page-title grow">My appointments</h1>
    <a class="btn btn-primary" href="<?= url('/patient/book') ?>">+ Book appointment</a>
</div>

<?php if ($upcoming): ?>
    <div class="card mt">
        <h2 class="card-title">Upcoming</h2>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Doctor</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($upcoming as $a): ?>
                        <tr>
                            <td><?= e(format_date($a['appointment_date'])) ?></td>
                            <td><strong><?= format_time($a['start_time']) ?></strong> – <?= format_time($a['end_time']) ?></td>
                            <td><?= e($a['doctor_name']) ?></td>
                            <td><?= e($a['department_name']) ?></td>
                            <td><span class="badge badge-<?= e($a['status']) ?>"><?= e($a['status']) ?></span></td>
                            <td class="text-right">
                                <?php if (in_array($a['status'], ['pending', 'approved', 'rescheduled'], true)): ?>
                                    <form method="post" action="<?= url('/patient/appointments/' . (int) $a['id'] . '/cancel') ?>" style="display:inline" data-confirm="Cancel this appointment?">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-sm btn-danger" type="submit">Cancel</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php else: ?>
    <div class="card mt"><p class="empty-state">No upcoming appointments. <a href="<?= url('/patient/book') ?>">Book one now</a>.</p></div>
<?php endif; ?>

<?php if ($history): ?>
    <div class="card mt">
        <h2 class="card-title">Past &amp; closed</h2>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Doctor</th>
                        <th>Department</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $a): ?>
                        <tr>
                            <td><?= e(format_date($a['appointment_date'])) ?></td>
                            <td><?= format_time($a['start_time']) ?></td>
                            <td><?= e($a['doctor_name']) ?></td>
                            <td><?= e($a['department_name']) ?></td>
                            <td><span class="badge badge-<?= e($a['status']) ?>"><?= e($a['status']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>