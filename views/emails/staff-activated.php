<?php
/**
 * Staff account activated.
 * @var string $role_name
 * @var string $login_email
 */
?>
<h2 style="margin:0 0 14px;color:#0f172a;">Your account is active</h2>
<p>Hello <?= e($user['name']) ?>,</p>
<p>Your account on <strong><?= e($app_name) ?></strong> has been approved and activated by the administrator. You can now sign in and start working as a <strong><?= e($role_name) ?></strong>.</p>
<table role="presentation" style="margin:18px 0;width:100%;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:14px 16px;">
    <tr><td style="padding:3px 0;color:#475569;">Sign-in email</td><td style="padding:3px 0;text-align:right;color:#0f172a;font-weight:600;"><?= e($login_email) ?></td></tr>
    <tr><td style="padding:3px 0;color:#475569;">Portal</td><td style="padding:3px 0;text-align:right;color:#0f766e;font-weight:600;"><?= e(url('/login')) ?></td></tr>
</table>
<p>Welcome aboard. If you did not expect this activation, please contact the system administrator.</p>