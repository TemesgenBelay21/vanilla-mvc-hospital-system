<?php
/** @var string $label */
/** @var string $value */
/** @var string $tone */
?><div class="stat-card<?= isset($tone) ? ' stat-' . e($tone) : '' ?>">
    <div class="stat-value"><?= e($value) ?></div>
    <div class="stat-label"><?= e($label) ?></div>
</div>