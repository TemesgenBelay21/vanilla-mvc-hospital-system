<div class="flex-between" style="align-items:flex-start;">
    <div>
        <h1 class="page-title">Invoice <?= e($invoice['invoice_number']) ?></h1>
        <p class="page-sub"><?= e($invoice['patient_name']) ?> &middot; <?= e(format_datetime($invoice['generated_at'])) ?></p>
    </div>
    <span class="badge badge-<?= e($invoice['status']) ?>" style="font-size:13px;padding:6px 14px;"><?= e(str_replace('_', ' ', $invoice['status'])) ?></span>
</div>

<div class="stat-grid">
    <?= render_partial('partials/stat-card', ['label' => 'Total', 'value' => number_format((float) $invoice['total_amount'], 2) . ' ETB', 'tone' => 'primary']) ?>
    <?= render_partial('partials/stat-card', ['label' => 'Paid', 'value' => number_format((float) $invoice['amount_paid'], 2) . ' ETB', 'tone' => 'ok']) ?>
    <?= render_partial('partials/stat-card', ['label' => 'Due', 'value' => number_format((float) $invoice['due_amount'], 2) . ' ETB', 'tone' => $invoice['due_amount'] > 0 ? 'warn' : 'ok']) ?>
</div>

<?php if ($invoice['due_amount'] > 0): ?>
<div class="card mt">
    <h2 class="card-title">Record cash payment</h2>
    <form method="post" action="<?= url('/accountant/invoices/' . (int) $invoice['id'] . '/pay-cash') ?>" class="form-inline">
        <?= csrf_field() ?>
        <input type="number" name="amount" min="0.01" max="<?= e(number_format((float) $invoice['due_amount'], 2, '.', '')) ?>" step="0.01"
               value="<?= e(number_format((float) $invoice['due_amount'], 2, '.', '')) ?>" required>
        <input type="text" name="reference" placeholder="Receipt / reference (optional)">
        <button class="btn btn-primary" type="submit">Record payment</button>
    </form>
</div>
<?php endif; ?>

<div class="card mt">
    <h2 class="card-title">Line items</h2>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Description</th><th class="text-right">Qty</th><th class="text-right">Unit price</th><th class="text-right">Line total</th></tr>
            </thead>
            <tbody>
                <?php if (!$items): ?>
                <tr><td colspan="4" class="text-muted">No line items on this invoice.</td></tr>
                <?php endif; ?>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= e($item['description']) ?></td>
                    <td class="text-right"><?= e((float) $item['quantity'] ? rtrim(rtrim(number_format((float) $item['quantity'], 2, '.', ''), '0'), '.') : '') ?></td>
                    <td class="text-right"><?= number_format((float) $item['unit_price'], 2) ?></td>
                    <td class="text-right"><?= number_format((float) $item['line_total'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-right" style="font-weight:600;">Subtotal</td>
                    <td class="text-right"><?= number_format((float) $invoice['subtotal'], 2) ?></td>
                </tr>
                <tr>
                    <td colspan="3" class="text-right" style="font-weight:600;">Total</td>
                    <td class="text-right"><?= number_format((float) $invoice['total_amount'], 2) ?> ETB</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div class="card mt">
    <h2 class="card-title">Payments (<?= count($payments) ?>)</h2>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Date</th><th>Amount</th><th>Method</th><th>Status</th><th>Reference</th><th>Recorded by</th></tr>
            </thead>
            <tbody>
                <?php if (!$payments): ?>
                <tr><td colspan="6" class="text-muted">No payments received yet.</td></tr>
                <?php endif; ?>
                <?php foreach ($payments as $p): ?>
                <tr>
                    <td><?= e(format_datetime($p['transaction_date'])) ?></td>
                    <td><?= number_format((float) $p['amount'], 2) ?> ETB</td>
                    <td><span class="badge badge-<?= $p['method'] === 'telebirr' ? 'info' : 'approved' ?>"><?= e($p['method']) ?></span></td>
                    <td><span class="badge badge-<?= $p['status'] === 'success' ? 'approved' : ($p['status'] === 'pending' ? 'pending' : 'rejected') ?>"><?= e($p['status']) ?></span></td>
                    <td><?= e($p['reference'] ?? ($p['telebirr_txn_no'] ?? '—')) ?></td>
                    <td><?= e($p['paid_by_name'] ?? '—') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<p class="mt"><a class="btn" href="<?= url('/accountant/invoices') ?>">&larr; Back to invoices</a></p>