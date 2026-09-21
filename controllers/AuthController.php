<?php

/**
 * AuthController — staff login and logout.
 *
 * This is an internal, staff-only system: patients never authenticate and
 * therefore have no registration path here.
 */

declare(strict_types=1);

class AuthController
{
    /** Landing route: send logged-in users to their dashboard. */
    public function redirectFromHome(): void
    {
        if (is_logged_in()) {
            redirect(role_home($_SESSION['role']));
        }
        redirect('/login');
    }

    public function loginForm(): void
    {
        if (is_logged_in()) {
            redirect(role_home($_SESSION['role']));
        }
        view('auth/login', [], 'guest');
    }

    public function login(): void
    {
        csrf_check();

        $email    = strtolower(trim($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        $user = User::findByEmail($email);

        if ($user === false || !password_verify($password, $user['password'])) {
            flash('error', 'Invalid email or password.');
            keep_old(['email' => $email]);
            redirect('/login');
        }

        if ($user['status'] !== 'active') {
            flash('error', 'This account has been disabled. Contact the administrator.');
            keep_old(['email' => $email]);
            redirect('/login');
        }

        User::updateLastLogin((int) $user['id']);
        set_auth_session($user);
        flash('success', 'Welcome back, ' . $user['name'] . '.');
        redirect(role_home($user['role']));
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        flash('info', 'You have been logged out.');
        redirect('/login');
    }
}