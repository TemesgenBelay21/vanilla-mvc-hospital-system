<div class="row">
    <h1 class="page-title grow">Patients</h1>
    <?php if (!$isAdmin): ?>
        <a class="btn btn-primary" href="<?= url('/receptionist/patients/create') ?>">+ Register patient</a>
    <?php endif; ?>
</div>

<div class="card">
    <form class="form-inline" method="get" action="<?= url('/' . ($isAdmin ? 'admin' : 'receptionist') . '/patients') ?>">
        <div class="grow">
            <input type="search" name="q" value="<?= e($q) ?>"
                   placeholder="Search by name, phone, or patient ID…">
        </div>
        <button class="btn btn-primary" type="submit">Search</button>
        <?php if ($q !== ''): ?>
            <a class="btn" href="<?= url('/' . ($isAdmin ? 'admin' : 'receptionist') . '/patients') ?>">Clear</a>
        <?php endif; ?>
    </form>

    <?php if ($patients): ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Gender</th>
                        <th>Blood type</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($patients as $p): ?>
                        <tr>
                            <td>#<?= (int) $p['id'] ?></td>
                            <td><a href="<?= url('/' . ($isAdmin ? 'admin' : 'receptionist') . '/patients/' . (int) $p['id'] . '/profile') ?>"><?= e($p['name']) ?></a></td>
                            <td><?= e($p['phone'] ?: '—') ?></td>
                            <td><?= e($p['email']) ?></td>
                            <td><?= e(ucfirst((string) $p['gender'])) ?></td>
                            <td><span class="badge badge-approved"><?= e($p['blood_type'] ?: '—') ?></span></td>
                            <td><span class="badge badge-<?= e($p['status']) ?>"><?= e($p['status']) ?></span></td>
                            <td class="text-right">
                                <a class="btn btn-sm" href="<?= url('/' . ($isAdmin ? 'admin' : 'receptionist') . '/patients/' . (int) $p['id'] . '/profile') ?>">Profile</a>
                                <?php if (!$isAdmin): ?>
                                    <a class="btn btn-sm btn-primary" href="<?= url('/receptionist/appointments/book?patient=' . (int) $p['id']) ?>">Book</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="empty-state">No patients found<?= $q !== '' ? ' for "' . e($q) . '"' : '' ?>.</p>
    <?php endif; ?>
</div>