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

<?php if ($isSandbox): ?>
<div class="card mt">
    <p class="text-muted" style="margin-bottom:14px;">
        <strong>Sandbox mode.</strong> This page simulates the Telebirr payment app on the patient's behalf — no real money moves.
        Clicking the button posts a signed callback to the real webhook endpoint, exactly as Telebirr would.
    </p>
    <form method="post" action="<?= url('/payment/telebirr/webhook') ?>">
        <?php foreach ($sandbox as $key => $value): ?>
        <input type="hidden" name="<?= e($key) ?>" value="<?= e($value) ?>">
        <?php endforeach; ?>
        <button class="btn btn-primary" type="submit">
            Simulate successful payment of <?= number_format((float) $invoice['due_amount'], 2) ?> ETB
        </button>
    </form>
</div>
<?php else: ?>
<div class="card mt">
    <p class="page-sub">You are being redirected to the Telebirr payment page. Approve the payment in the patient's Telebirr app.</p>
    <p class="mt"><a class="btn" href="<?= url($verifyUrl) ?>">Payment done? Check status</a></p>
</div>
<?php endif; ?>

<p class="mt"><a class="btn" href="<?= url($backPath) ?>">&larr; Back to invoices</a></p>
