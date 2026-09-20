<?php
/**
 * Lab result ready.
 * @var string $patient_name
 * @var string|null $test_name
 * @var string $result_date
 * @var string $result_link
 */
?>
<h2 style="margin:0 0 14px;color:#0f172a;">Lab result ready</h2>
<p>Hello <?= e($patient_name) ?>,</p>
<p>Your lab <?= $test_name ? ('result for <strong>' . e($test_name) . '</strong>') : 'result' ?> is ready and has been reviewed on <strong><?= e(date('F j, Y g:i A', strtotime($result_date))) ?></strong>.</p>
<p>You can view the full report from your patient portal:</p>
<p>
    <a href="<?= e($result_link) ?>" style="display:inline-block;background:#0f766e;color:#ffffff;padding:10px 20px;border-radius:6px;text-decoration:none;font-weight:600;">View result</a>
</p>
<p>Please share any questions about the report with your attending physician at your next visit.</p>