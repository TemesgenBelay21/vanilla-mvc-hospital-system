<h1 class="page-title">Telebirr payment</h1>
<p class="page-sub">Invoice <?= e($invoice['invoice_number']) ?></p>

<div class="card">
    <div class="def-list">
        <dt>Invoice</dt>
        <dd><?= e($invoice['invoice_number']) ?></dd>
        <dt>Patient</dt>
        <dd><?= e($invoice['patient_name']) ?></dd>
        <dt>Amount due</dt>
        <dd><strong><?= number_format((float) $invoice['due_amount'], 2) ?> ETB</strong></dd>
    </div>
</div>

<?php if ($isSandbox): $cb = $sandbox; ?>
<div class="card mt">
    <div class="alert alert-warning" style="margin-bottom:14px;">
        <strong>Sandbox mode.</strong> This page simulates the Telebirr payment app — no real money moves.
        Clicking the button below posts a signed callback to the real webhook endpoint, exactly as Telebirr would.
    </div>
    <form method="post" action="<?= url('/payment/telebirr/webhook') ?>">
        <?php foreach ($cb as $key => $value): ?>
            <input type="hidden" name="<?= e($key) ?>" value="<?= e($value) ?>">
        <?php endforeach; ?>
        <button class="btn btn-primary" type="submit">
            Simulate successful payment of <?= number_format((float) $invoice['due_amount'], 2) ?> ETB
        </button>
    </form>
</div>
<?php else: ?>
<div class="card mt">
    <p>You are being redirected to the Telebirr payment page. Please approve the payment in your Telebirr app.</p>
    <meta http-equiv="refresh" content="0;url=<?= e(url('/patient/invoices')) ?>">
    <a class="btn" href="<?= url('/patient/telebirr/verify?invoice=' . (int) $invoice['id']) ?>">Payment done? Check status</a>
</div>
<?php endif; ?>

<p class="mt"><a class="btn" href="<?= url('/patient/invoices') ?>">&larr; Back to my invoices</a></p>