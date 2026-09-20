<div class="row">
    <h1 class="page-title grow">Appointments</h1>
    <a class="btn btn-primary" href="<?= url('/receptionist/appointments/book') ?>">+ Book appointment</a>
</div>

<div class="card">
    <form class="form-inline" method="get" action="<?= url('/receptionist/appointments') ?>">
        <label for="status">Status</label>
        <select id="status" name="status" style="max-width:180px">
            <option value="">All statuses</option>
            <?php foreach ($statuses as $s): ?>
                <option value="<?= e($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option>
            <?php endforeach; ?>
        </select>
        <label for="date">Date</label>
        <input type="date" id="date" name="date" value="<?= e($date) ?>" style="max-width:180px">
        <button class="btn btn-primary" type="submit">Filter</button>
        <?php if ($status !== '' || $date !== ''): ?>
            <a class="btn" href="<?= url('/receptionist/appointments') ?>">Clear</a>
        <?php endif; ?>
    </form>

    <?php if ($rows): ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $a): ?>
                        <tr>
                            <td><?= e(format_date($a['appointment_date'])) ?></td>
                            <td><strong><?= format_time($a['start_time']) ?></strong></td>
                            <td><?= e($a['patient_name']) ?> <span class="text-muted">(<?= e($a['patient_phone'] ?: '—') ?>)</span></td>
                            <td><?= e($a['doctor_name']) ?></td>
                            <td><?= e($a['department_name']) ?></td>
                            <td><span class="badge badge-<?= e($a['status']) ?>"><?= e($a['status']) ?></span></td>
                            <td class="text-right">
                                <?php if (in_array($a['status'], ['pending', 'rescheduled'], true)): ?>
                                    <form method="post" action="<?= url('/receptionist/appointments/' . (int) $a['id'] . '/approve') ?>" style="display:inline">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-sm btn-ok" type="submit">Approve</button>
                                    </form>
                                    <form method="post" action="<?= url('/receptionist/appointments/' . (int) $a['id'] . '/reject') ?>" style="display:inline" data-confirm="Reject this appointment?">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-sm btn-danger" type="submit">Reject</button>
                                    </form>
                                    <a class="btn btn-sm" href="<?= url('/receptionist/appointments/' . (int) $a['id'] . '/reschedule') ?>">Reschedule</a>
                                <?php elseif ($a['status'] === 'approved'): ?>
                                    <a class="btn btn-sm" href="<?= url('/receptionist/appointments/' . (int) $a['id'] . '/reschedule') ?>">Reschedule</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="empty-state">No appointments match your filters.</p>
    <?php endif; ?>
</div>