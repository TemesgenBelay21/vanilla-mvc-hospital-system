<h1 class="page-title">Billing dashboard</h1>
<p class="page-sub">Invoice reconciliation and collections.</p>

<div class="stat-grid">
    <?= render_partial('partials/stat-card', ['label' => 'Invoices', 'value' => $invoices, 'tone' => 'primary']) ?>
    <?= render_partial('partials/stat-card', ['label' => 'Paid', 'value' => $paid, 'tone' => 'ok']) ?>
    <?= render_partial('partials/stat-card', ['label' => 'Partially paid', 'value' => $partial, 'tone' => 'warn']) ?>
    <?= render_partial('partials/stat-card', ['label' => 'Unpaid', 'value' => $unpaid, 'tone' => 'danger']) ?>
    <?= render_partial('partials/stat-card', ['label' => 'Outstanding (ETB)', 'value' => number_format($outstanding, 2), 'tone' => 'teal']) ?>
    <?= render_partial('partials/stat-card', ['label' => 'This month (ETB)', 'value' => number_format($monthRevenue, 2), 'tone' => 'ok']) ?>
</div>

<div class="card mt">
    <h2 class="card-title">Quick actions</h2>
    <div class="quick-actions">
        <a class="btn btn-primary" href="<?= url('/accountant/invoices') ?>">Review invoices</a>
        <a class="btn" href="<?= url('/accountant/invoices/generate') ?>">Generate invoice</a>
        <a class="btn" href="<?= url('/accountant/payments') ?>">Payment history</a>
    </div>
</div>

<?php if ($recent): ?>
<div class="card mt">
    <h2 class="card-title">Recent payments</h2>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Invoice</th><th>Patient</th><th>Amount</th><th>Method</th><th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent as $p): ?>
                <tr>
                    <td><a href="<?= url('/accountant/invoices/' . (int) $p['invoice_id']) ?>"><?= e($p['invoice_number']) ?></a></td>
                    <td><?= e($p['patient_name']) ?></td>
                    <td><?= number_format((float) $p['amount'], 2) ?> ETB</td>
                    <td><span class="badge badge-<?= $p['method'] === 'telebirr' ? 'info' : 'approved' ?>"><?= e($p['method']) ?></span></td>
                    <td><?= e(format_datetime($p['transaction_date'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>