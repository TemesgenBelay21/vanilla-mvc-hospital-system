<?php
/**
 * Appointment rescheduled.
 * @var string $doctor_name
 * @var string $patient_name
 * @var string $old_date
 * @var string $new_date
 * @var string $new_time
 */
?>
<h2 style="margin:0 0 14px;color:#0f172a;">Appointment rescheduled</h2>
<p>Hello <?= e($patient_name) ?>,</p>
<p>Your appointment with <strong>Dr. <?= e($doctor_name) ?></strong> has been rescheduled.</p>
<table role="presentation" style="margin:18px 0;width:100%;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:14px 16px;">
    <tr><td style="padding:3px 0;color:#475569;">Previous date</td><td style="padding:3px 0;text-align:right;color:#94a3b8;text-decoration:line-through;"><?= e(date('F j, Y g:i A', strtotime($old_date))) ?></td></tr>
    <tr><td style="padding:3px 0;color:#475569;">New date</td><td style="padding:3px 0;text-align:right;color:#0f172a;font-weight:600;"><?= e(date('F j, Y', strtotime($new_date))) ?></td></tr>
    <tr><td style="padding:3px 0;color:#475569;">New time</td><td style="padding:3px 0;text-align:right;color:#0f172a;font-weight:600;"><?= e(date('g:i A', strtotime($new_time))) ?></td></tr>
</table>
<p>If this no longer works for you, please contact the reception desk to book another slot.</p>