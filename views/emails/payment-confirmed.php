<?php
/**
 * Invoice payment confirmed.
 * @var string $invoice_number
 * @var string $amount
 * @var string $reference
 */
?>
<h2 style="margin:0 0 14px;color:#0f172a;">Payment received</h2>
<p>Hello <?= e($user['name']) ?>,</p>
<p>We are pleased to confirm we received your payment. Your invoice is now settled.</p>
<table role="presentation" style="margin:18px 0;width:100%;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:14px 16px;">
    <tr><td style="padding:3px 0;color:#475569;">Invoice</td><td style="padding:3px 0;text-align:right;color:#0f172a;font-weight:600;"><?= e($invoice_number) ?></td></tr>
    <tr><td style="padding:3px 0;color:#475569;">Amount paid</td><td style="padding:3px 0;text-align:right;color:#0f766e;font-weight:600;"><?= e($amount) ?> ETB</td></tr>
    <tr><td style="padding:3px 0;color:#475569;">Transaction reference</td><td style="padding:3px 0;text-align:right;color:#0f172a;font-weight:600;"><?= e($reference) ?></td></tr>
</table>
<p>Thank you for choosing <?= e($app_name) ?>. A copy of your invoice is available in your patient portal.</p>