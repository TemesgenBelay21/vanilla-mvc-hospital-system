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

/** Redirect to an application path and stop execution. */
function redirect(string $path): void
{
    header('Location: ' . url($path));
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
        case 'admin':        return '/admin';
        case 'doctor':       return '/doctor';
        case 'receptionist': return '/receptionist';
        case 'patient':      return '/patient';
        default:             return '/login';
    }
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