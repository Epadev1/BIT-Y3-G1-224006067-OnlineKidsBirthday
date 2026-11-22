<?php
declare(strict_types=1);

final class UserController
{
    public static function index(): void
    {
        require_admin();
        render('users/index', ['title' => 'Manage users', 'users' => User::all()]);
    }

    public static function create(): void
    {
        require_admin();
        render('users/form', ['title' => 'Add user', 'person' => null, 'errors' => []]);
    }

    public static function store(): void
    {
        require_admin();
        [$values, $errors] = self::validatedInput();
        if ($errors) {
            render('users/form', ['title' => 'Add user', 'person' => null, 'errors' => $errors, 'values' => $values]);
            return;
        }
        User::create($values['username'], $values['email'], $values['password'], $values['role']);
        flash('success', 'User created.');
        redirect('users');
    }

    public static function edit(): void
    {
        require_admin();
        $person = User::find(request_id());
        if (!$person) {
            throw new HttpException(404, 'User not found.');
        }
        render('users/form', ['title' => 'Edit user', 'person' => $person, 'errors' => []]);
    }

    public static function update(): void
    {
        require_admin();
        $id = post_int('user_id');
        $person = User::find($id);
        if (!$person) {
            throw new HttpException(404, 'User not found.');
        }
        [$values, $errors] = self::validatedInput($id);
        if ($id === (int) current_user()['user_id'] && $values['role'] !== 'admin') {
            $errors[] = 'You cannot remove your own administrator role.';
        }
        if ($errors) {
            $values['user_id'] = $id;
            render('users/form', ['title' => 'Edit user', 'person' => $values, 'errors' => $errors, 'values' => $values]);
            return;
        }
        User::update($id, $values['username'], $values['email'], $values['role'], $values['password'] !== '' ? $values['password'] : null);
        flash('success', 'User updated.');
        redirect('users');
    }

    public static function delete(): void
    {
        require_admin();
        $id = post_int('user_id');
        if (!$id || $id === (int) current_user()['user_id'] || !User::find($id)) {
            throw new HttpException(400, 'This user cannot be deleted.');
        }
        User::delete($id);
        flash('success', 'User and their party records were deleted.');
        redirect('users');
    }

    private static function validatedInput(int $ignoreId = 0): array
    {
        $values = [
            'username' => post_string('username'),
            'email' => strtolower(post_string('email')),
            'password' => post_raw_string('password'),
            'role' => post_string('role'),
        ];
        $errors = [];
        if (strlen($values['username']) < 2 || strlen($values['username']) > 80) {
            $errors[] = 'Name must be between 2 and 80 characters.';
        }
        if (!validate_email($values['email']) || strlen($values['email']) > 190) {
            $errors[] = 'Enter a valid email.';
        } else {
            $existing = User::findByEmail($values['email']);
            if ($existing && (int) $existing['user_id'] !== $ignoreId) {
                $errors[] = 'That email is already in use.';
            }
        }
        if ($ignoreId === 0 && !valid_password($values['password'])) {
            $errors[] = 'Password must be between 8 and 72 bytes.';
        } elseif ($values['password'] !== '' && !valid_password($values['password'])) {
            $errors[] = 'New password must be between 8 and 72 bytes.';
        }
        if (!in_array($values['role'], ['parent', 'admin'], true)) {
            $errors[] = 'Choose a valid role.';
        }
        return [$values, $errors];
    }
}
