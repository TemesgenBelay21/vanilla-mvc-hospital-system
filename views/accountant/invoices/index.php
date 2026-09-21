<h1 class="page-title">Invoices</h1>
<p class="page-sub">Filter by status, patient or date range.</p>

<div class="card" style="margin-bottom:18px;">
    <form method="get" action="<?= url($roleHome . '/invoices') ?>" class="form-inline">
        <select name="status">
            <option value="">All statuses</option>
            <?php foreach ($statuses as $s): ?>
                <option value="<?= e($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= e(ucwords(str_replace('_', ' ', $s))) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="text" name="q" value="<?= e($q) ?>" placeholder="Patient name or invoice #">
        <input type="date" name="from" value="<?= e($from) ?>">
        <input type="date" name="to" value="<?= e($to) ?>">
        <button class="btn btn-primary" type="submit">Filter</button>
        <a class="btn" href="<?= url($roleHome . '/invoices') ?>">Reset</a>
        <?php if ($isAccountant): ?>
        <a class="btn" href="<?= url('/accountant/invoices/generate') ?>">+ Generate invoice</a>
        <?php endif; ?>
    </form>
</div>

<div class="stat-grid">
    <?= render_partial('partials/stat-card', ['label' => 'Total', 'value' => $stats['total'], 'tone' => 'primary']) ?>
    <?= render_partial('partials/stat-card', ['label' => 'Paid', 'value' => $stats['paid'], 'tone' => 'ok']) ?>
    <?= render_partial('partials/stat-card', ['label' => 'Partially paid', 'value' => $stats['partial'], 'tone' => 'warn']) ?>
    <?= render_partial('partials/stat-card', ['label' => 'Unpaid', 'value' => $stats['unpaid'], 'tone' => 'danger']) ?>
    <?= render_partial('partials/stat-card', ['label' => 'Outstanding (ETB)', 'value' => number_format($stats['outstanding'], 2), 'tone' => 'teal']) ?>
</div>

<div class="card mt">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Invoice #</th><th>Patient</th><th>Date</th>
                    <th class="text-right">Total</th><th class="text-right">Paid</th>
                    <th class="text-right">Due</th><th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$invoices): ?>
                <tr><td colspan="7" class="text-muted">No invoices match your filters.</td></tr>
                <?php endif; ?>
                <?php foreach ($invoices as $inv): ?>
                <tr>
                    <td><a href="<?= url($roleHome . '/invoices/' . (int) $inv['id']) ?>"><?= e($inv['invoice_number']) ?></a></td>
                    <td><?= e($inv['patient_name']) ?></td>
                    <td><?= e(format_date($inv['generated_at'])) ?></td>
                    <td class="text-right"><?= number_format((float) $inv['total_amount'], 2) ?></td>
                    <td class="text-right"><?= number_format((float) $inv['amount_paid'], 2) ?></td>
                    <td class="text-right"><?= number_format((float) $inv['due_amount'], 2) ?></td>
                    <td><span class="badge badge-<?= e($inv['status']) ?>"><?= e(str_replace('_', ' ', $inv['status'])) ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>