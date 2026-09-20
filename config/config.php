<?php

/**
 * Global application configuration.
 * Loaded first by the front controller (public/index.php).
 */

declare(strict_types=1);

// ---------------------------------------------------------------------------
// Error reporting (development defaults — tighten in production)
// ---------------------------------------------------------------------------
error_reporting(E_ALL);
ini_set('display_errors', '1');

// ---------------------------------------------------------------------------
// Timezone
// ---------------------------------------------------------------------------
date_default_timezone_set('Africa/Addis_Ababa');

// ---------------------------------------------------------------------------
// Application constants
// ---------------------------------------------------------------------------
define('APP_NAME', 'Almaz General Hospital');
define('APP_ROOT', dirname(__DIR__));            // project root
define('APP_VERSION', '1.0.0');

// Fixed appointment slot length in minutes (Phase 1 scope)
define('SLOT_MINUTES', 30);

// ---------------------------------------------------------------
// Phase 3 — billing fees (ETB default price list; editable here)
// ---------------------------------------------------------------
define('FEE_CONSULTATION', 500.00);   // per completed appointment
define('FEE_LAB_TEST',     250.00);   // per completed lab request
define('FEE_WARD_DAILY',   800.00);   // per admitted day (admission -> discharge)

// ---------------------------------------------------------------
// Phase 3 — Telebirr payment configuration
// All credentials come from environment variables (never hardcoded).
// TELEBIRR_MODE = 'sandbox' | 'live'. In sandbox mode the app simulates
// the full flow locally so the feature can be tested without real
// merchant credentials.
// ---------------------------------------------------------------
define('TELEBIRR_MODE',      getenv('TELEBIRR_MODE')      ?: 'sandbox');
define('TELEBIRR_APP_ID',    getenv('TELEBIRR_APP_ID')    ?: '');
define('TELEBIRR_APP_KEY',   getenv('TELEBIRR_APP_KEY')   ?: '');
define('TELEBIRR_PUBLIC_KEY', getenv('TELEBIRR_PUBLIC_KEY') ?: '');
define('TELEBIRR_PRIVATE_KEY', getenv('TELEBIRR_PRIVATE_KEY') ?: '');
define('TELEBIRR_SHORT_CODE', getenv('TELEBIRR_SHORT_CODE') ?: '');
define('TELEBIRR_API_BASE',   getenv('TELEBIRR_API_BASE')  ?: 'https://sandbox-telebirr.ethiotelecom.et');

// ---------------------------------------------------------------
// Phase 3 — SMTP mail configuration (PHPMailer)
// ---------------------------------------------------------------
define('MAIL_HOST',      getenv('MAIL_HOST') ?: 'smtp.gmail.com');
define('MAIL_PORT',      (int) (getenv('MAIL_PORT') ?: 587));
define('MAIL_USER',      getenv('MAIL_USER') ?: '');
define('MAIL_PASS',      getenv('MAIL_PASS') ?: '');
define('MAIL_FROM',      getenv('MAIL_FROM') ?: 'no-reply@almazhospital.et');
define('MAIL_FROM_NAME', getenv('MAIL_FROM_NAME') ?: 'Almaz General Hospital');

// Allowed select values
define('GENDERS', ['male', 'female', 'other']);
define('BLOOD_TYPES', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']);
define('APPOINTMENT_STATUSES', ['pending', 'approved', 'rejected', 'rescheduled', 'completed']);

// ---------------------------------------------------------------------------
// Database credentials
// ---------------------------------------------------------------------------
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'hospital_management_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ---------------------------------------------------------------------------
// Session configuration
// ---------------------------------------------------------------------------
session_name('hms_session');
session_set_cookie_params([
    'lifetime' => 0,                    // browser-session cookie
    'path'     => '/',
    'domain'   => '',
    'secure'   => false,                // HTTPS not used locally
    'httponly' => true,
    'samesite' => 'Lax',
]);

// Reasonable idle timeout (seconds) — 30 minutes
define('SESSION_TIMEOUT', 30 * 60);

// ---------------------------------------------------------------------------
// Base URL detection (works both under htdocs subfolders and vhost doc roots)
// ---------------------------------------------------------------------------
$documentRoot = str_replace('\\', '/', rtrim(realpath($_SERVER['DOCUMENT_ROOT'] ?? APP_ROOT), '/'));
$appRoot      = str_replace('\\', '/', APP_ROOT);
$publicRoot   = str_replace('\\', '/', rtrim(realpath(APP_ROOT . '/public'), '/'));

if ($documentRoot === $publicRoot) {
    // Document root is public/ itself (e.g. `php -S -t public` or vhost -> public)
    $basePath = '';
} elseif ($documentRoot !== $appRoot && strpos($appRoot, $documentRoot) === 0) {
    // App lives inside the document root (e.g. htdocs/vanilla-mvc-hospital-system)
    $basePath = substr($appRoot, strlen($documentRoot)) . '/public';
} else {
    // Document root is the project root
    $basePath = '/public';
}

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host   = $_SERVER['HTTP_HOST'] ?? 'localhost';

define('BASE_URL', rtrim($scheme . '://' . $host . $basePath, '/'));
define('BASE_PATH', rtrim($basePath, '/'));

// ---------------------------------------------------------------------------
// Class autoloader
//   controllers/UserController.php  -> class UserController
//   models/User.php                 -> class User
//   models/Appointment.php          -> class Appointment
// ---------------------------------------------------------------------------
spl_autoload_register(function (string $class): void {
    foreach (['controllers', 'models', 'services', 'services/telebirr'] as $dir) {
        $file = APP_ROOT . '/' . $dir . '/' . $class . '.php';
        if (is_file($file)) {
            require_once $file;
            return;
        }
    }
});

// ---------------------------------------------------------------------------
// Small PDO accessor used across controllers/models
// ---------------------------------------------------------------------------
require_once APP_ROOT . '/config/database.php';

function db(): PDO
{
    return Database::getInstance();
}

// ---------------------------------------------------------------------------
// Start the session (with a sane idle timeout, enforced in helpers on demand)
// ---------------------------------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---------------------------------------------------------------------------
// Load helpers (auth guards, CSRF, flash, e(), redirect, …)
// ---------------------------------------------------------------------------
require_once APP_ROOT . '/config/helpers.php';