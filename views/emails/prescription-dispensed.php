<?php
/**
 * Prescription dispensed.
 * @var string $patient_name
 * @var string|null $prescription_note
 * @var string $medicine_names
 * @var string $dispensed_date
 */
?>
<h2 style="margin:0 0 14px;color:#0f172a;">Prescription dispensed</h2>
<p>Hello <?= e($patient_name) ?>,</p>
<p>Your prescription has been <strong>dispensed</strong> by the hospital pharmacy on <strong><?= e(date('F j, Y g:i A', strtotime($dispensed_date))) ?></strong>.</p>
<table role="presentation" style="margin:18px 0;width:100%;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:14px 16px;">
    <tr><td style="padding:3px 0;color:#475569;">Medicines</td><td style="padding:3px 0;text-align:right;color:#0f172a;font-weight:600;"><?= e($medicine_names) ?></td></tr>
    <?php if (!empty($prescription_note)): ?>
    <tr><td style="padding:3px 0;color:#475569;">Instructions</td><td style="padding:3px 0;text-align:right;color:#0f172a;"><?= e($prescription_note) ?></td></tr>
    <?php endif; ?>
</table>
<p>Please follow the dosage instructions carefully and complete the full course.</p>