<div class="row">
    <h1 class="page-title grow">Departments</h1>
    <a class="btn btn-primary" href="<?= url('/admin/departments/create') ?>">+ New department</a>
</div>

<div class="card mt">
    <?php if ($departments): ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Department</th>
                        <th>Description</th>
                        <th>Doctors</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($departments as $d): ?>
                        <tr>
                            <td><strong><?= e($d['name']) ?></strong></td>
                            <td style="white-space:normal"><?= e($d['description'] ?: '—') ?></td>
                            <td><span class="badge badge-approved"><?= (int) $d['doctor_count'] ?></span></td>
                            <td class="text-right">
                                <a class="btn btn-sm" href="<?= url('/admin/departments/' . (int) $d['id'] . '/edit') ?>">Edit</a>
                                <form method="post" action="<?= url('/admin/departments/' . (int) $d['id'] . '/delete') ?>" data-confirm="Delete department '<?= e($d['name']) ?>'?" style="display:inline">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm btn-danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="empty-state">No departments yet. Create the first one.</p>
    <?php endif; ?>
</div>