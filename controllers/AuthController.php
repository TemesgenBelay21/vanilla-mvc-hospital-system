<?php

/**
 * AuthController — login, logout and patient self-registration.
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

    public function registerForm(): void
    {
        if (is_logged_in()) {
            redirect(role_home($_SESSION['role']));
        }
        view('auth/register', [], 'guest');
    }

    public function register(): void
    {
        csrf_check();

        $name     = trim($_POST['name'] ?? '');
        $email    = strtolower(trim($_POST['email'] ?? ''));
        $phone    = trim($_POST['phone'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        $confirm  = (string) ($_POST['password_confirmation'] ?? '');

        $errors = [];

        if (mb_strlen($name) < 3) {
            $errors[] = 'Full name must be at least 3 characters.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid email address is required.';
        } elseif (User::findByEmail($email) !== false) {
            $errors[] = 'That email address is already registered.';
        }
        if ($phone !== '' && mb_strlen($phone) < 7) {
            $errors[] = 'Please enter a valid phone number.';
        }
        if (mb_strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        } elseif ($password !== $confirm) {
            $errors[] = 'Passwords do not match.';
        }

        if ($errors) {
            foreach ($errors as $error) {
                flash('error', $error);
            }
            keep_old(['name' => $name, 'email' => $email, 'phone' => $phone]);
            redirect('/register');
        }

        $userId = User::create([
            'role'     => 'patient',
            'name'     => $name,
            'email'    => $email,
            'password' => $password,
            'phone'    => $phone ?: null,
        ]);
        Patient::createFor($userId, []);

        set_auth_session(User::findById($userId));
        flash('success', 'Account created. Welcome to ' . APP_NAME . '!');
        redirect('/patient');
    }
}