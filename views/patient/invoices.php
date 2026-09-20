<h1 class="page-title">My invoices</h1>
<p class="page-sub">You currently have <strong><?= number_format($outstanding, 2) ?> ETB</strong> outstanding.</p>

<div class="card mt">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Invoice #</th><th>Date</th><th class="text-right">Total</th>
                    <th class="text-right">Paid</th><th class="text-right">Due</th><th>Status</th><th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$invoices): ?>
                <tr><td colspan="7" class="text-muted">You have no invoices yet.</td></tr>
                <?php endif; ?>
                <?php foreach ($invoices as $inv): ?>
                <tr>
                    <td><?= e($inv['invoice_number']) ?></td>
                    <td><?= e(format_date($inv['generated_at'])) ?></td>
                    <td class="text-right"><?= number_format((float) $inv['total_amount'], 2) ?></td>
                    <td class="text-right"><?= number_format((float) $inv['amount_paid'], 2) ?></td>
                    <td class="text-right"><?= number_format((float) $inv['due_amount'], 2) ?></td>
                    <td><span class="badge badge-<?= e($inv['status']) ?>"><?= e(str_replace('_', ' ', $inv['status'])) ?></span></td>
                    <td>
                        <form method="post" action="<?= url('/patient/invoices/' . (int) $inv['id'] . '/pay') ?>">
                            <?= csrf_field() ?>
                            <button class="btn btn-primary btn-sm" type="submit" <?= $inv['due_amount'] <= 0 ? 'disabled' : '' ?>>
                                <?= $inv['due_amount'] > 0 ? 'Pay with Telebirr' : 'Paid' ?>
                            </button>
                        </form>
                        <?php if ($inv['due_amount'] > 0): ?>
                        <a class="btn btn-sm" href="<?= url('/patient/telebirr/verify?invoice=' . (int) $inv['id']) ?>">Check status</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<p class="mt text-muted" style="font-size:13px;">
    Payments are processed securely via Telebirr. Your invoice updates automatically once payment is confirmed.
</p>