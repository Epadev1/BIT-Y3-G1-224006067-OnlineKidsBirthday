<?php
declare(strict_types=1);

final class HomeController
{
    public static function index(): void
    {
        $vendors = Vendor::all();
        $user = current_user();
        $parties = $user ? Party::allForUser($user) : [];
        $nextParty = null;
        foreach ($parties as $party) {
            if ($party['party_date'] >= date('Y-m-d')) {
                $nextParty = $party;
                break;
            }
        }
        render('home', [
            'title' => 'Plan a kids’ birthday',
            'vendors' => array_slice($vendors, 0, 3),
            'vendorCount' => Vendor::countAll(),
            'partyCount' => Party::countAll(),
            'bookingCount' => Booking::countAll(),
            'nextParty' => $nextParty,
        ]);
    }
}
