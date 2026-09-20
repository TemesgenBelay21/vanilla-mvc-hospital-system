<?php

/**
 * Shared helper functions: escaping, redirects, flash messages,
 * CSRF protection and role-based authentication guards.
 */

declare(strict_types=1);

// ---------------------------------------------------------------------------
// Output / URL helpers
// ---------------------------------------------------------------------------

/** HTML-escape a value for safe output. */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** Build an absolute application URL. */
function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

/** Redirect to an application path (or absolute URL) and stop execution. */
function redirect(string $path): void
{
    if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
        header('Location: ' . $path);
    } else {
        header('Location: ' . url($path));
    }
    exit;
}

// ---------------------------------------------------------------------------
// Flash messages
// ---------------------------------------------------------------------------

/** Queue a one-shot flash message ('success' | 'error' | 'info' | 'warning'). */
function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

/** Pull all queued flash messages (and clear them). */
function pull_flashes(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

/** Remember a form value after a failed submit (re-populate inputs). */
function old(string $key, string $fallback = ''): string
{
    $value = $_SESSION['old'][$key] ?? $fallback;
    unset($_SESSION['old'][$key]);
    return e($value);
}

function keep_old(array $input): void
{
    $_SESSION['old'] = $_input = array_merge($_SESSION['old'] ?? [], $input);
}

// ---------------------------------------------------------------------------
// CSRF protection
// ---------------------------------------------------------------------------

/** Return (creating if needed) the session CSRF token. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Hidden input for forms. */
function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

/**
 * Validate the submitted CSRF token.
 * Aborts with a 403 page if invalid/missing.
 */
function csrf_check(): void
{
    $posted = $_POST['_token'] ?? '';
    if (!hash_equals(csrf_token(), (string) $posted)) {
        http_response_code(403);
        flash('error', 'Invalid security token. Please try again.');
        redirect($_SERVER['HTTP_REFERER'] ?? '/');
    }
}

// ---------------------------------------------------------------------------
// Session timeout
// ---------------------------------------------------------------------------

/** Enforce the idle-session timeout. */
function enforce_session_timeout(): void
{
    if (isset($_SESSION['last_activity'])) {
        if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
            $_SESSION = [];
            session_destroy();
            flash('info', 'Your session expired. Please log in again.');
            redirect('/login');
        }
    }
    $_SESSION['last_activity'] = time();
}

// ---------------------------------------------------------------------------
// Authentication helpers
// ---------------------------------------------------------------------------

/** Store the logged-in user (array/object) in the session and regenerate id. */
function set_auth_session(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['role']    = $user['role'];
    $_SESSION['user']    = $user;
    $_SESSION['last_activity'] = time();
}

/** Return the currently logged-in user or false. */
function current_user()
{
    enforce_session_timeout();
    return $_SESSION['user'] ?? false;
}

/** True when a user is logged in. */
function is_logged_in(): bool
{
    return !empty($_SESSION['user']);
}

/** Guard: require an authenticated user. */
function require_login(): array
{
    $user = current_user();
    if ($user === false) {
        flash('info', 'Please log in to continue.');
        redirect('/login');
    }
    return $user;
}

/** Guard: require one of the given roles. */
function require_role(string ...$roles): array
{
    $user = require_login();
    if (!in_array($user['role'], $roles, true)) {
        flash('error', 'You are not allowed to access that page.');
        redirect('/');
    }
    return $user;
}

/** Role-specific dashboard path used after login. */
function role_home(string $role): string
{
    switch ($role) {
        case 'admin':          return '/admin';
        case 'doctor':         return '/doctor';
        case 'receptionist':   return '/receptionist';
        case 'patient':        return '/patient';
        case 'nurse':          return '/nurse';
        case 'pharmacist':     return '/pharmacist';
        case 'lab_technician': return '/lab';
        case 'accountant':     return '/accountant';
        default:               return '/login';
    }
}

// ---------------------------------------------------------------------------
// View rendering
// ---------------------------------------------------------------------------

/** Render a view partial into a string. */
function render_partial(string $view, array $data = []): string
{
    ob_start();
    extract($data, EXTR_SKIP);
    require APP_ROOT . '/views/' . $view . '.php';
    return ob_get_clean();
}

/**
 * Render a page inside a layout ('app' for the sidebar shell, 'guest' for
 * standalone centered pages, 'none' for raw output).
 */
function view(string $view, array $data = [], string $layout = 'app'): void
{
    $content = render_partial($view, $data);
    if ($layout === 'none') {
        echo $content;
        return;
    }
    $user = current_user();
    require APP_ROOT . '/views/layouts/' . $layout . '.php';
}

/** Send a JSON response and stop. */
function json_response(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

// ---------------------------------------------------------------------------
// File uploads
// ---------------------------------------------------------------------------

/**
 * Handle an optional image upload. Returns the stored relative path on success,
 * null when the field was empty (keep existing), or flash('error') + die on invalid.
 */
function handle_photo_upload(array $file, string $subdir): ?string
{
    $maxBytes = 2 * 1024 * 1024; // 2 MB

    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        flash('error', 'Photo upload failed (error ' . $file['error'] . ').');
        redirect($_SERVER['HTTP_REFERER'] ?? '/admin/staff');
    }
    if ($file['size'] > $maxBytes) {
        flash('error', 'Photo must be 2 MB or smaller.');
        redirect($_SERVER['HTTP_REFERER'] ?? '/admin/staff');
    }

    $info     = getimagesize($file['tmp_name']);
    $extByMime = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
    ];
    if ($info === false || !isset($extByMime[$info['mime']])) {
        flash('error', 'Photo must be a JPEG, PNG, GIF or WebP image.');
        redirect($_SERVER['HTTP_REFERER'] ?? '/admin/staff');
    }

    $dir = APP_ROOT . '/public/uploads/' . trim($subdir, '/');
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    $name = date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $extByMime[$info['mime']];
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
        flash('error', 'Could not save the uploaded photo.');
        redirect($_SERVER['HTTP_REFERER'] ?? '/admin/staff');
    }
    return 'uploads/' . trim($subdir, '/') . '/' . $name;
}

// ---------------------------------------------------------------------------
// Misc formatter helpers
// ---------------------------------------------------------------------------

function format_date(?string $date): string
{
    if (!$date) return '—';
    $dt = date_create($date);
    return $dt ? $dt->format('M j, Y') : '—';
}

function format_datetime(?string $datetime): string
{
    if (!$datetime) return '—';
    $dt = date_create($datetime);
    return $dt ? $dt->format('M j, Y g:i A') : '—';
}

/** Time "HH:MM:SS" -> "g:i A". */
function format_time(?string $time): string
{
    if (!$time) return '—';
    $dt = date_create('2000-01-01 ' . $time);
    return $dt ? $dt->format('g:i A') : '—';
}

function time_to(string $time, int $minutes = SLOT_MINUTES): string
{
    $dt = date_create('2000-01-01 ' . $time);
    return $dt ? $dt->modify("+{$minutes} minutes")->format('H:i:s') : $time;
}

const WEEKDAYS = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

/** Badge tone for a user role (consistent coloring across staff lists). */
function role_badge(string $role): string
{
    return [
        'admin'          => 'danger',
        'doctor'         => 'rescheduled',
        'receptionist'   => 'info',
        'nurse'          => 'approved',
        'pharmacist'     => 'pending',
        'lab_technician' => 'completed',
        'accountant'     => 'warning',
        'patient'        => 'info',
    ][$role] ?? 'info';
}

/** Create an in-app notification for a user (Phase 3 notification bell). */
function notify_user(int $userId, string $title, string $message, ?string $link = null): void
{
    Notification::create($userId, $title, $message, $link);
}

/** In-app notification + branded email for the same event. */
function notify_and_mail(int $userId, string $title, string $message, ?string $link, string $template, array $data = []): void
{
    notify_user($userId, $title, $message, $link);
    EmailService::send($userId, $title, $template, $data);
}