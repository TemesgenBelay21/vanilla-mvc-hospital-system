<h1 class="page-title">Payment history</h1>
<p class="page-sub">Latest <?= count($payments) ?> recorded payments.</p>

<div class="card mt">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Invoice</th><th>Patient</th><th>Amount</th><th>Method</th><th>Status</th><th>Reference</th><th>Date</th></tr>
            </thead>
            <tbody>
                <?php if (!$payments): ?>
                <tr><td colspan="7" class="text-muted">No payments recorded yet.</td></tr>
                <?php endif; ?>
                <?php foreach ($payments as $p): ?>
                <tr>
                    <td><a href="<?= url('/accountant/invoices/' . (int) $p['invoice_id']) ?>"><?= e($p['invoice_number']) ?></a></td>
                    <td><?= e($p['patient_name']) ?></td>
                    <td><?= number_format((float) $p['amount'], 2) ?> ETB</td>
                    <td><span class="badge badge-<?= $p['method'] === 'telebirr' ? 'info' : 'approved' ?>"><?= e($p['method']) ?></span></td>
                    <td><span class="badge badge-<?= $p['status'] === 'success' ? 'approved' : ($p['status'] === 'pending' ? 'pending' : 'rejected') ?>"><?= e($p['status']) ?></span></td>
                    <td><?= e($p['reference'] ?? ($p['telebirr_txn_no'] ?? '—')) ?></td>
                    <td><?= e(format_datetime($p['transaction_date'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>