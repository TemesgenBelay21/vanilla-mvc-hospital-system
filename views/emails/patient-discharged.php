<?php
/**
 * Patient discharged.
 * @var string $patient_name
 * @var string $discharge_date
 * @var string|null $invoice_number
 * @var string|null $total_amount
 */
?>
<h2 style="margin:0 0 14px;color:#0f172a;">Discharge complete</h2>
<p>Hello <?= e($user['name']) ?>,</p>
<p><strong><?= e($patient_name) ?></strong> has been discharged from <?= e($app_name) ?> on <strong><?= e(date('F j, Y', strtotime($discharge_date))) ?></strong>.</p>
<?php if (!empty($invoice_number)): ?>
<table role="presentation" style="margin:18px 0;width:100%;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:14px 16px;">
    <tr><td style="padding:3px 0;color:#475569;">Invoice</td><td style="padding:3px 0;text-align:right;color:#0f172a;font-weight:600;"><?= e($invoice_number) ?></td></tr>
    <tr><td style="padding:3px 0;color:#475569;">Total amount</td><td style="padding:3px 0;text-align:right;color:#0f172a;font-weight:600;"><?= e($total_amount) ?> ETB</td></tr>
    <tr><td style="padding:3px 0;color:#475569;">Pay online</td><td style="padding:3px 0;text-align:right;color:#0f766e;font-weight:600;">Staff billing desk (Telebirr in person)</td></tr>
</table>
<p>Outstanding balances can be settled at the hospital billing desk via cash or Telebirr.</p>
<?php else: ?>
<p>Thank you for staying with us. We wish <?= e($patient_name) ?> a speedy recovery.</p>
<?php endif; ?>