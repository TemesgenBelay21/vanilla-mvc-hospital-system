<?php
/**
 * Appointment approved.
 * @var string $doctor_name
 * @var string $patient_name
 * @var string $date
 * @var string $time
 */
?>
<h2 style="margin:0 0 14px;color:#0f172a;">Appointment approved</h2>
<p>Hello <?= e($patient_name) ?>,</p>
<p>Your appointment with <strong>Dr. <?= e($doctor_name) ?></strong> has been <strong>approved</strong>.</p>
<table role="presentation" style="margin:18px 0;width:100%;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:14px 16px;">
    <tr><td style="padding:3px 0;color:#475569;">Date</td><td style="padding:3px 0;text-align:right;color:#0f172a;font-weight:600;"><?= e(date('F j, Y', strtotime($date))) ?></td></tr>
    <tr><td style="padding:3px 0;color:#475569;">Time</td><td style="padding:3px 0;text-align:right;color:#0f172a;font-weight:600;"><?= e(date('g:i A', strtotime($time))) ?></td></tr>
    <tr><td style="padding:3px 0;color:#475569;">Doctor</td><td style="padding:3px 0;text-align:right;color:#0f172a;font-weight:600;">Dr. <?= e($doctor_name) ?></td></tr>
</table>
<p>Please arrive a few minutes early and bring your appointment reference. If you need to reschedule, visit your patient portal.</p>