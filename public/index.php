<?php

/**
 * Front controller / bootstrap.
 *
 * Phase 1 (commit 1) status page: verifies configuration and database.
 * The URL router is introduced alongside authentication.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

$dbOk      = false;
$dbError   = null;
$tableList = [];

try {
    $stmt   = db()->query('SHOW TABLES');
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $dbOk   = true;
    $tableList = $tables;
} catch (Throwable $e) {
    $dbError = $e->getMessage();
}

header('Content-Type: text/html; charset=utf-8');
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body>
    <main class="setup">
        <h1><?= e(APP_NAME) ?></h1>
        <p><?= e(APP_VERSION) ?> — <strong>Phase 1</strong> scaffold installed.</p>

        <section class="card">
            <h2>Database status</h2>
            <?php if ($dbOk): ?>
                <p class="ok">Connected to <code><?= e(DB_NAME) ?></code></p>
                <p>Tables found: <?= count($tableList) ? implode(', ', array_map('e', $tableList)) : 'none yet' ?></p>
            <?php else: ?>
                <p class="err">Could not connect: <?= e($dbError ?? 'unknown') ?></p>
            <?php endif; ?>
        </section>

        <p>Run <code>database/schema.sql</code> then <code>database/seed.sql</code> against MySQL if tables are missing.</p>
    </main>
</body>
</html>