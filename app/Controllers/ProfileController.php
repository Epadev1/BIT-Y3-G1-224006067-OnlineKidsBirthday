<?php
declare(strict_types=1);

final class ProfileController
{
    public static function edit(): void
    {
        $user = require_login();
        $person = User::find((int) $user['user_id']);
        if (!$person) {
            throw new HttpException(404, 'Account not found.');
        }
        render('profile/edit', ['title' => 'Your profile', 'person' => $person, 'errors' => []]);
    }

    public static function update(): void
    {
        $user = require_login();
        $person = User::find((int) $user['user_id']);
        if (!$person) {
            throw new HttpException(404, 'Account not found.');
        }

        $values = [
            'username' => post_string('username'),
            'email' => strtolower(post_string('email')),
            'current_password' => post_raw_string('current_password'),
            'new_password' => post_raw_string('new_password'),
            'confirm_password' => post_raw_string('confirm_password'),
        ];
        $errors = [];

        if (strlen($values['username']) < 2 || strlen($values['username']) > 80) {
            $errors[] = 'Your name must be between 2 and 80 characters.';
        }
        if (!validate_email($values['email']) || strlen($values['email']) > 190) {
            $errors[] = 'Enter a valid email address.';
        } else {
            $existing = User::findByEmail($values['email']);
            if ($existing && (int) $existing['user_id'] !== (int) $person['user_id']) {
                $errors[] = 'That email address is already in use.';
            }
        }

        $changingPassword = $values['new_password'] !== '' || $values['confirm_password'] !== '';
        if ($changingPassword) {
            if ($values['current_password'] === '') {
                $errors[] = 'Enter your current password before choosing a new one.';
            } else {
                $account = User::findByEmail($person['email']);
                if (!$account || !password_verify($values['current_password'], $account['password_hash'])) {
                    $errors[] = 'Your current password is not correct.';
                }
            }
            if (!valid_password($values['new_password'])) {
                $errors[] = 'The new password must be between 8 and 72 bytes.';
            }
            if ($values['new_password'] !== $values['confirm_password']) {
                $errors[] = 'The new passwords do not match.';
            }
        }

        if ($errors) {
            $values['user_id'] = $person['user_id'];
            $values['role'] = $person['role'];
            $values['created_at'] = $person['created_at'];
            render('profile/edit', [
                'title' => 'Your profile',
                'person' => $values,
                'errors' => $errors,
                'values' => $values,
            ]);
            return;
        }

        User::update(
            (int) $person['user_id'],
            $values['username'],
            $values['email'],
            $person['role'],
            $changingPassword ? $values['new_password'] : null
        );

        $_SESSION['user']['username'] = $values['username'];
        $_SESSION['user']['email'] = $values['email'];
        $_SESSION['last_activity'] = time();
        if ($changingPassword) {
            session_regenerate_id(true);
            $_SESSION['user'] = User::find((int) $person['user_id']);
        }
        flash('success', 'Your profile has been updated.');
        redirect('profile');
    }
}
