<?php
declare(strict_types=1);

final class User
{
    public static function findByEmail(string $email): ?array
    {
        $statement = db()->prepare('SELECT user_id, username, email, password_hash, role FROM users WHERE email = ?');
        $statement->execute([$email]);
        $user = $statement->fetch();
        return $user ?: null;
    }

    public static function find(int $id): ?array
    {
        $statement = db()->prepare('SELECT user_id, username, email, role, created_at FROM users WHERE user_id = ?');
        $statement->execute([$id]);
        $user = $statement->fetch();
        return $user ?: null;
    }

    public static function all(): array
    {
        return db()->query('SELECT user_id, username, email, role, created_at FROM users ORDER BY created_at DESC')->fetchAll();
    }

    public static function create(string $username, string $email, string $password, string $role = 'parent'): int
    {
        $statement = db()->prepare('INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)');
        $statement->execute([$username, $email, password_hash($password, PASSWORD_DEFAULT), $role]);
        return (int) db()->lastInsertId();
    }

    public static function update(int $id, string $username, string $email, string $role, ?string $password): void
    {
        if ($password !== null) {
            $statement = db()->prepare('UPDATE users SET username = ?, email = ?, role = ?, password_hash = ? WHERE user_id = ?');
            $statement->execute([$username, $email, $role, password_hash($password, PASSWORD_DEFAULT), $id]);
            return;
        }
        $statement = db()->prepare('UPDATE users SET username = ?, email = ?, role = ? WHERE user_id = ?');
        $statement->execute([$username, $email, $role, $id]);
    }

    public static function delete(int $id): void
    {
        $statement = db()->prepare('DELETE FROM users WHERE user_id = ?');
        $statement->execute([$id]);
    }
}
