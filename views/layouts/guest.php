<?php
/**
 * Guest layout — standalone centered pages (login, register, 404).
 * @var string $content
 */
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
    <script>window.APP_BASE = <?= json_encode(BASE_URL) ?>;</script>
</head>
<body class="guest">
    <div class="auth-wrap">
        <div class="auth-brand">
            <span class="brand-mark">+</span>
            <span class="brand-text"><?= e(APP_NAME) ?></span>
        </div>
        <main class="auth-card">
            <?php foreach (pull_flashes() as $flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
            <?php endforeach; ?>
            <?= $content ?>
        </main>
    </div>
    <script src="<?= url('assets/js/main.js') ?>"></script>
</body>
</html>