<?php
/**
 * Patient admitted.
 * @var string $patient_name
 * @var string $ward
 * @var string $bed
 * @var string $doctor_name
 * @var string $admission_date
 */
?>
<h2 style="margin:0 0 14px;color:#0f172a;">Admission confirmed</h2>
<p>Hello <?= e($user['name']) ?>,</p>
<p>We are pleased to inform you that <strong><?= e($patient_name) ?></strong> has been admitted to <strong><?= e($app_name) ?></strong>.</p>
<table role="presentation" style="margin:18px 0;width:100%;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:14px 16px;">
    <tr><td style="padding:3px 0;color:#475569;">Admission date</td><td style="padding:3px 0;text-align:right;color:#0f172a;font-weight:600;"><?= e(date('F j, Y g:i A', strtotime($admission_date))) ?></td></tr>
    <tr><td style="padding:3px 0;color:#475569;">Ward</td><td style="padding:3px 0;text-align:right;color:#0f172a;font-weight:600;"><?= e($ward) ?></td></tr>
    <tr><td style="padding:3px 0;color:#475569;">Bed</td><td style="padding:3px 0;text-align:right;color:#0f172a;font-weight:600;"><?= e($bed) ?></td></tr>
    <tr><td style="padding:3px 0;color:#475569;">Attending doctor</td><td style="padding:3px 0;text-align:right;color:#0f172a;font-weight:600;">Dr. <?= e($doctor_name) ?></td></tr>
</table>
<p>The nursing team will be looking after them round the clock. For updates, please contact the ward directly.</p>