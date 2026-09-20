<div class="row">
    <h1 class="page-title grow">Weekly availability</h1>
</div>
<p class="page-sub text-muted"><?= e($doctor['name']) ?> — set the fixed 30-minute slots patients can book.</p>

<div class="card">
    <h2 class="card-title">Add a slot</h2>
    <form class="form-inline" method="post" action="<?= url('/doctor/availability/store') ?>">
        <?= csrf_field() ?>
        <label for="day_of_week">Day</label>
        <select id="day_of_week" name="day_of_week" style="max-width:200px">
            <?php foreach ($days as $d): ?>
                <option value="<?= $d ?>" <?= (int) date('w') === $d ? 'selected' : '' ?>><?= e(WEEKDAYS[$d]) ?></option>
            <?php endforeach; ?>
        </select>
        <label for="start_time">Starting time</label>
        <select id="start_time" name="start_time" style="max-width:160px">
            <?php foreach ($times as $t): ?>
                <option value="<?= e($t) ?>"><?= format_time($t) ?> – <?= format_time(time_to($t)) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-primary" type="submit">Add slot</button>
    </form>
</div>

<div class="card mt">
    <h2 class="card-title">Schedule</h2>
    <?php $hasAny = false; ?>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Day</th><th>Slots</th><th class="text-right">Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($days as $d): ?>
                    <?php $slots = $byDay[$d] ?? []; ?>
                    <?php if ($slots) $hasAny = true; ?>
                    <tr>
                        <td><strong><?= e(WEEKDAYS[$d]) ?></strong></td>
                        <td style="white-space:normal">
                            <?php if ($slots): ?>
                                <?php foreach ($slots as $s): ?>
                                    <span class="chip">
                                        <?= format_time($s['start_time']) ?> – <?= format_time(time_to($s['start_time'])) ?>
                                        <form method="post" action="<?= url('/doctor/availability/' . (int) $s['id'] . '/destroy') ?>" style="display:inline" data-confirm="Remove this slot?">
                                            <?= csrf_field() ?>
                                            <button class="chip-x" type="submit" title="Remove slot">&times;</button>
                                        </form>
                                    </span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="text-muted">No availability</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-right">—</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if (!$hasAny): ?>
        <p class="text-muted">Add slots above to enable appointment booking for your patients.</p>
    <?php endif; ?>
</div>