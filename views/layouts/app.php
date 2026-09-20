<?php
/**
 * Main application layout — collapsible sidebar + header + content.
 * @var string $content
 */
$user    = current_user();
$role    = (string) ($_SESSION['role'] ?? $user['role']);
$current = '/' . trim($_GET['url'] ?? '', '/');
$isRoot  = ($_GET['url'] ?? '') === '' || trim($_GET['url'] ?? '', '/') === 'index.php';
$current = $isRoot ? '/' . $role : $current;

$nav = [];
switch ($role) {
    case 'admin':
        $nav = [
            'Dashboard'    => '/admin',
            'Departments'  => '/admin/departments',
            'Staff Accounts' => '/admin/staff',
            'Patients'     => '/admin/patients',
            'Wards'        => '/admin/wards',
            'Admissions'   => '/admin/admissions',
        ];
        break;
    case 'receptionist':
        $nav = [
            'Dashboard'    => '/receptionist',
            'Patients'     => '/receptionist/patients',
            'Appointments' => '/receptionist/appointments',
        ];
        break;
    case 'doctor':
        $nav = [
            'Dashboard'    => '/doctor',
            'My Appointments' => '/doctor/appointments',
            'Availability' => '/doctor/availability',
        ];
        break;
    case 'patient':
        $nav = [
            'Dashboard'      => '/patient',
            'Book Appointment' => '/patient/book',
            'My Appointments'=> '/patient/appointments',
        ];
    break;
    case 'nurse':
        $nav = [
            'Dashboard'    => '/nurse',
            'My Wards'     => '/nurse/wards',
            'Admissions'   => '/nurse/admissions',
        ];
        break;
    case 'pharmacist':
        $nav = [
            'Dashboard'    => '/pharmacist',
        ];
        break;
    case 'lab_technician':
        $nav = [
            'Dashboard'    => '/lab',
        ];
        break;
}

function nav_active(string $page, string $current): string
{
    if ($page === '/admin' && $current === '/admin') return ' active';
    if ($page !== '/' && strpos($current . '/', $page . '/') === 0) return ' active';
    return '';
}

$roleLabel = ucfirst($role);
$initials  = strtoupper(substr(preg_replace('/[^A-Za-z ]/', '', $user['name'] ?? 'U'), 0, 2));
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($roleLabel) ?> — <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
    <script>window.APP_BASE = <?= json_encode(BASE_URL) ?>;</script>
</head>
<body id="app" class="app">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <span class="brand-mark">+</span>
            <span class="brand-text"><?= e(APP_NAME) ?></span>
        </div>
        <nav class="sidebar-nav">
            <?php foreach ($nav as $label => $href): ?>
                <a class="nav-link<?= nav_active($href, $current) ?>" href="<?= url($href) ?>"><?= e($label) ?></a>
            <?php endforeach; ?>
        </nav>
        <div class="sidebar-footer">
            <a class="nav-link" href="<?= url('/logout') ?>">Logout</a>
        </div>
    </aside>

    <div class="main">
        <header class="topbar">
            <button class="icon-btn" id="sidebarToggle" aria-label="Toggle navigation">&#9776;</button>
            <div class="topbar-title"><?= e($roleLabel) ?></div>
            <div class="topbar-user">
                <span class="avatar" title="<?= e($user['name']) ?>"><?= e($initials) ?></span>
                <span class="user-meta">
                    <span class="user-name"><?= e($user['name']) ?></span>
                    <span class="user-role"><?= e($roleLabel) ?></span>
                </span>
            </div>
        </header>

        <main class="content">
            <?php foreach (pull_flashes() as $flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
            <?php endforeach; ?>
            <?= $content ?>
        </main>
    </div>

    <script src="<?= url('assets/js/main.js') ?>"></script>
</body>
</html>