<div class="row">
    <h1 class="page-title grow">Patients in my care</h1>
</div>

<div class="card">
    <form class="form-inline" method="get" action="<?= url('/doctor/patients') ?>">
        <div class="grow">
            <input type="search" name="q" value="<?= e($q) ?>"
                   placeholder="Search by name, phone, or patient ID…">
        </div>
        <button class="btn btn-primary" type="submit">Search</button>
        <?php if ($q !== ''): ?>
            <a class="btn" href="<?= url('/doctor/patients') ?>">Clear</a>
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
                        <th>Blood type</th>
                        <th>Last visit</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($patients as $p): ?>
                        <tr>
                            <td>#<?= (int) $p['id'] ?></td>
                            <td><a href="<?= url('/doctor/patients/' . (int) $p['id'] . '/profile') ?>"><?= e($p['name']) ?></a></td>
                            <td><?= e($p['phone'] ?: '—') ?></td>
                            <td><?= e($p['email']) ?></td>
                            <td><span class="badge badge-approved"><?= e($p['blood_type'] ?: '—') ?></span></td>
                            <td class="text-muted"><?= $p['last_visit'] ? e(format_date($p['last_visit'])) : '—' ?></td>
                            <td class="text-right">
                                <a class="btn btn-sm" href="<?= url('/doctor/patients/' . (int) $p['id'] . '/profile') ?>">Profile</a>
                                <a class="btn btn-sm btn-primary" href="<?= url('/doctor/patients/' . (int) $p['id'] . '/prescriptions/new') ?>">Prescribe</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="empty-state">No matching patients. Patients you see for appointments, admit, or treat appear here.</p>
    <?php endif; ?>
</div>

<p class="text-muted mt">"In my care" means you have an appointment, admission, prescription or lab record with the patient.</p>