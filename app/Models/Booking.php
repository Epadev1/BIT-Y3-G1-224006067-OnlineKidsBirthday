<?php
declare(strict_types=1);

final class Booking
{
    public static function countAll(): int
    {
        return (int) db()->query('SELECT COUNT(*) FROM bookings')->fetchColumn();
    }

    public static function allForUser(array $user): array
    {
        $sql = 'SELECT b.*, p.child_name, p.party_date, p.parent_id, u.username AS parent_name,
                       v.name AS vendor_name, v.service_type
                FROM bookings b JOIN parties p ON p.party_id = b.party_id
                JOIN users u ON u.user_id = p.parent_id
                JOIN vendors v ON v.vendor_id = b.vendor_id';
        if ($user['role'] === 'admin') {
            return db()->query($sql . ' ORDER BY b.booking_id DESC')->fetchAll();
        }
        $statement = db()->prepare($sql . ' WHERE p.parent_id = ? ORDER BY b.booking_id DESC');
        $statement->execute([$user['user_id']]);
        return $statement->fetchAll();
    }

    public static function forParty(int $partyId): array
    {
        $statement = db()->prepare(
            'SELECT b.*, v.name AS vendor_name, v.service_type
             FROM bookings b JOIN vendors v ON v.vendor_id = b.vendor_id
             WHERE b.party_id = ? ORDER BY b.booking_id DESC'
        );
        $statement->execute([$partyId]);
        return $statement->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $statement = db()->prepare(
            'SELECT b.*, p.parent_id FROM bookings b JOIN parties p ON p.party_id = b.party_id WHERE b.booking_id = ?'
        );
        $statement->execute([$id]);
        $booking = $statement->fetch();
        return $booking ?: null;
    }

    public static function create(int $partyId, int $vendorId, float $amount): int
    {
        $statement = db()->prepare('INSERT INTO bookings (party_id, vendor_id, amount, status) VALUES (?, ?, ?, ?)');
        $statement->execute([$partyId, $vendorId, $amount, 'pending']);
        return (int) db()->lastInsertId();
    }

    public static function existsForPartyAndVendor(int $partyId, int $vendorId, int $exceptBookingId = 0): bool
    {
        $sql = 'SELECT 1 FROM bookings WHERE party_id = ? AND vendor_id = ?';
        $parameters = [$partyId, $vendorId];
        if ($exceptBookingId > 0) {
            $sql .= ' AND booking_id <> ?';
            $parameters[] = $exceptBookingId;
        }
        $statement = db()->prepare($sql);
        $statement->execute($parameters);
        return (bool) $statement->fetchColumn();
    }

    public static function update(int $id, int $partyId, int $vendorId, float $amount, string $status): void
    {
        $statement = db()->prepare(
            'UPDATE bookings SET party_id = ?, vendor_id = ?, amount = ?, status = ? WHERE booking_id = ?'
        );
        $statement->execute([$partyId, $vendorId, $amount, $status, $id]);
    }

    public static function updateStatus(int $id, string $status): void
    {
        $statement = db()->prepare('UPDATE bookings SET status = ? WHERE booking_id = ?');
        $statement->execute([$status, $id]);
    }

    public static function delete(int $id): void
    {
        $statement = db()->prepare('DELETE FROM bookings WHERE booking_id = ?');
        $statement->execute([$id]);
    }
}
