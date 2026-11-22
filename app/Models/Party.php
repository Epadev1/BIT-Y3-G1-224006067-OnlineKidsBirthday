<?php
declare(strict_types=1);

final class Party
{
    public static function countAll(): int
    {
        return (int) db()->query('SELECT COUNT(*) FROM parties')->fetchColumn();
    }

    public static function allForUser(array $user): array
    {
        if ($user['role'] === 'admin') {
            return db()->query(
                'SELECT p.*, u.username AS parent_name FROM parties p JOIN users u ON u.user_id = p.parent_id ORDER BY p.party_date'
            )->fetchAll();
        }
        $statement = db()->prepare(
            'SELECT p.*, u.username AS parent_name FROM parties p JOIN users u ON u.user_id = p.parent_id WHERE p.parent_id = ? ORDER BY p.party_date'
        );
        $statement->execute([$user['user_id']]);
        return $statement->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $statement = db()->prepare(
            'SELECT p.*, u.username AS parent_name FROM parties p JOIN users u ON u.user_id = p.parent_id WHERE p.party_id = ?'
        );
        $statement->execute([$id]);
        $party = $statement->fetch();
        return $party ?: null;
    }

    public static function create(int $parentId, string $child, string $date, string $theme, float $budget): int
    {
        $statement = db()->prepare('INSERT INTO parties (parent_id, child_name, party_date, theme, budget) VALUES (?, ?, ?, ?, ?)');
        $statement->execute([$parentId, $child, $date, $theme, $budget]);
        return (int) db()->lastInsertId();
    }

    public static function update(int $id, string $child, string $date, string $theme, float $budget): void
    {
        $statement = db()->prepare('UPDATE parties SET child_name = ?, party_date = ?, theme = ?, budget = ? WHERE party_id = ?');
        $statement->execute([$child, $date, $theme, $budget, $id]);
    }

    public static function delete(int $id): void
    {
        $statement = db()->prepare('DELETE FROM parties WHERE party_id = ?');
        $statement->execute([$id]);
    }

    public static function belongsTo(int $id, array $user): bool
    {
        $party = self::find($id);
        return $party && ($user['role'] === 'admin' || (int) $party['parent_id'] === (int) $user['user_id']);
    }
}
