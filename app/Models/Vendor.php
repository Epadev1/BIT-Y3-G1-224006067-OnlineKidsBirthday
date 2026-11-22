<?php
declare(strict_types=1);

final class Vendor
{
    public const SERVICE_TYPES = ['Cake', 'Decorations', 'Entertainment', 'Photography'];

    private const ORDER_BY = "ORDER BY CASE service_type WHEN 'Decorations' THEN 1 WHEN 'Cake' THEN 2 WHEN 'Entertainment' THEN 3 ELSE 4 END, name";

    public static function all(string $search = '', string $serviceType = ''): array
    {
        $sql = 'SELECT vendor_id, name, service_type, price, contact_email FROM vendors';
        $conditions = [];
        $parameters = [];

        if ($search !== '') {
            $conditions[] = '(name LIKE ? OR service_type LIKE ?)';
            $like = '%' . $search . '%';
            $parameters[] = $like;
            $parameters[] = $like;
        }
        if (in_array($serviceType, self::SERVICE_TYPES, true)) {
            $conditions[] = 'service_type = ?';
            $parameters[] = $serviceType;
        }
        if ($conditions) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $statement = db()->prepare($sql . ' ' . self::ORDER_BY);
        $statement->execute($parameters);
        return $statement->fetchAll();
    }

    public static function countAll(): int
    {
        return (int) db()->query('SELECT COUNT(*) FROM vendors')->fetchColumn();
    }

    public static function find(int $id): ?array
    {
        $statement = db()->prepare('SELECT vendor_id, name, service_type, price, contact_email FROM vendors WHERE vendor_id = ?');
        $statement->execute([$id]);
        $vendor = $statement->fetch();
        return $vendor ?: null;
    }

    public static function create(string $name, string $service, float $price, string $email): int
    {
        $statement = db()->prepare('INSERT INTO vendors (name, service_type, price, contact_email) VALUES (?, ?, ?, ?)');
        $statement->execute([$name, $service, $price, $email]);
        return (int) db()->lastInsertId();
    }

    public static function update(int $id, string $name, string $service, float $price, string $email): void
    {
        $statement = db()->prepare('UPDATE vendors SET name = ?, service_type = ?, price = ?, contact_email = ? WHERE vendor_id = ?');
        $statement->execute([$name, $service, $price, $email, $id]);
    }

    public static function delete(int $id): void
    {
        $statement = db()->prepare('DELETE FROM vendors WHERE vendor_id = ?');
        $statement->execute([$id]);
    }
}
