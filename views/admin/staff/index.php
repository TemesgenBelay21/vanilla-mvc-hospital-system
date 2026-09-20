<div class="row">
    <h1 class="page-title grow">Staff accounts</h1>
    <a class="btn btn-primary" href="<?= url('/admin/staff/doctors/create') ?>">+ New doctor</a>
    <a class="btn" href="<?= url('/admin/staff/receptionists/create') ?>">+ New receptionist</a>
</div>

<div class="card mt">
    <?php if ($staff): ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Role</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Department</th>
                        <th>Specialization</th>
                        <th>Status</th>
                        <th>Last login</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($staff as $s): ?>
                        <tr>
                            <td><span class="badge badge-<?= $s['role'] === 'doctor' ? 'rescheduled' : 'info' ?>"><?= e($s['role']) ?></span></td>
                            <td>
                                <div class="row nopad" style="gap:8px">
                                    <?php if ($s['photo']): ?>
                                        <img class="mini-avatar" src="<?= url($s['photo']) ?>" alt="">
                                    <?php endif; ?>
                                    <a href="<?= url('/admin/staff/' . (int) $s['id'] . '/edit') ?>"><?= e($s['name']) ?></a>
                                </div>
                            </td>
                            <td><?= e($s['email']) ?></td>
                            <td><?= e($s['phone'] ?: '—') ?></td>
                            <td><?= e($s['department_name'] ?? '—') ?></td>
                            <td><?= e($s['specialization'] ?? '—') ?></td>
                            <td><span class="badge badge-<?= e($s['status']) ?>"><?= e($s['status']) ?></span></td>
                            <td><?= format_datetime($s['last_login_at']) ?></td>
                            <td class="text-right">
                                <a class="btn btn-sm" href="<?= url('/admin/staff/' . (int) $s['id'] . '/edit') ?>">Edit</a>
                                <form method="post" action="<?= url('/admin/staff/' . (int) $s['id'] . '/toggle-status') ?>" style="display:inline">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm btn-<?= $s['status'] === 'active' ? 'danger' : 'ok' ?>" type="submit">
                                        <?= $s['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="empty-state">No staff accounts yet.</p>
    <?php endif; ?>
</div>