<?php
declare(strict_types=1);

final class AuthController
{
    public static function registerForm(): void
    {
        if (current_user()) {
            redirect('home');
        }
        render('auth/register', ['title' => 'Create your account']);
    }

    public static function register(): void
    {
        $username = post_string('username');
        $email = strtolower(post_string('email'));
        $password = post_raw_string('password');
        $errors = [];

        if (strlen($username) < 2 || strlen($username) > 80) {
            $errors[] = 'Your name must be between 2 and 80 characters.';
        }
        if (!validate_email($email) || strlen($email) > 190) {
            $errors[] = 'Enter a valid email address.';
        } elseif (User::findByEmail($email)) {
            $errors[] = 'An account already uses that email address.';
        }
        if (!valid_password($password)) {
            $errors[] = 'Password must be between 8 and 72 bytes.';
        }
        if ($errors) {
            render('auth/register', ['title' => 'Create your account', 'errors' => $errors]);
            return;
        }

        $id = User::create($username, $email, $password);
        establish_session(['user_id' => $id, 'username' => $username, 'email' => $email, 'role' => 'parent']);
        flash('success', 'Welcome! Your account is ready.');
        redirect('home');
    }

    public static function loginForm(): void
    {
        if (current_user()) {
            redirect('home');
        }
        render('auth/login', ['title' => 'Welcome back']);
    }

    public static function login(): void
    {
        $email = strtolower(post_string('email'));
        $password = post_raw_string('password');
        $user = User::findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            render('auth/login', [
                'title' => 'Welcome back',
                'errors' => ['Email or password is incorrect.'],
                'email' => $email,
            ]);
            return;
        }

        establish_session($user);
        flash('success', 'You are signed in.');
        redirect('home');
    }

    public static function logout(): void
    {
        unset($_SESSION['user']);
        session_regenerate_id(true);
        flash('success', 'You have signed out.');
        redirect('home');
    }
}
