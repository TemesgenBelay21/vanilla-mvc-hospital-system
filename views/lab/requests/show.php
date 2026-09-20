<div class="row">
    <h1 class="page-title grow">
        Lab request #<?= (int) $request['id'] ?>
        <?= $request['priority'] === 'urgent' ? '<span class="badge badge-rejected">Urgent</span>' : '' ?>
    </h1>
    <a class="btn" href="<?= url('/lab/requests') ?>">&larr; Back to requests</a>
</div>

<div class="grid mt">
    <div class="card">
        <h3 class="card-title">Patient &amp; test</h3>
        <p>
            <strong><?= e($request['patient_name']) ?></strong><br>
            <span class="muted"><?= e($request['phone']) ?></span><br>
            Test: <strong><?= e($request['test_name']) ?></strong>
        </p>
        <?php if ($request['notes']): ?>
            <p class="muted"><strong>Notes:</strong> <?= e($request['notes']) ?></p>
        <?php endif; ?>
    </div>
    <div class="card">
        <h3 class="card-title">Requested by</h3>
        <p>
            <?= e($request['doctor_name']) ?><br>
            <span class="muted">on <?= e(format_date($request['requested_at'])) ?></span>
        </p>
        <p>
            Status:
            <?php if ($request['status'] === 'requested'): ?>
                <span class="badge badge-pending">Requested</span>
            <?php elseif ($request['status'] === 'in_progress'): ?>
                <span class="badge badge-active">In progress</span>
            <?php else: ?>
                <span class="badge badge-completed">Completed</span>
            <?php endif; ?>
        </p>
    </div>
</div>

<?php if ($request['status'] === 'requested'): ?>
<div class="card mt">
    <h3 class="card-title">Start processing</h3>
    <form method="post" action="<?= url('/lab/requests/' . (int) $request['id'] . '/start') ?>">
        <?= csrf_field() ?>
        <p class="muted">Mark this sample as being processed.</p>
        <button class="btn btn-primary" type="submit">Start processing</button>
    </form>
</div>
<?php endif; ?>

<?php if ($request['status'] !== 'completed'): ?>
<div class="card mt">
    <h3 class="card-title">Record result</h3>
    <form method="post" action="<?= url('/lab/requests/' . (int) $request['id'] . '/complete') ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="result_text">Result text</label>
            <textarea id="result_text" name="result_text" rows="4"
                      placeholder="Findings, values, interpretation…"></textarea>
        </div>
        <div class="form-group">
            <label for="result_file">Result file <span class="optional">(PDF, PNG or JPG, up to 2MB)</span></label>
            <input type="file" id="result_file" name="result_file" accept=".pdf,.png,.jpg,.jpeg">
        </div>
        <button class="btn btn-ok" type="submit">Complete request</button>
    </form>
</div>
<?php endif; ?>

<?php if ($request['status'] === 'completed'): ?>
<div class="card mt">
    <h3 class="card-title">Result</h3>
    <?php if ($request['result_text']): ?>
        <p><?= nl2br(e($request['result_text'])) ?></p>
    <?php endif; ?>
    <?php if ($request['result_file']): ?>
        <a class="btn btn-sm" href="<?= url('/lab/files/' . (int) $request['id'] . '/download') ?>">&darr; Download result file</a>
    <?php endif; ?>
    <p class="muted">Completed <?= e(format_date($request['completed_at'])) ?> by <?= e($request['completed_by_name'] ?: 'laboratory') ?></p>
</div>
<?php endif; ?>