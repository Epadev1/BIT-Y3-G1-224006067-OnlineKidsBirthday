<?php
declare(strict_types=1);

final class BookingController
{
    public static function index(): void
    {
        $user = require_login();
        render('bookings/index', ['title' => 'Bookings', 'bookings' => Booking::allForUser($user)]);
    }

    public static function create(): void
    {
        $user = require_login();
        $partyId = filter_input(INPUT_GET, 'party_id', FILTER_VALIDATE_INT) ?: 0;
        if ($partyId && !Party::belongsTo($partyId, $user)) {
            throw new HttpException(404, 'Party not found.');
        }
        $vendorId = filter_input(INPUT_GET, 'vendor_id', FILTER_VALIDATE_INT) ?: 0;
        if ($vendorId && !Vendor::find($vendorId)) {
            throw new HttpException(404, 'Vendor not found.');
        }
        render('bookings/create', [
            'title' => 'Create a booking',
            'parties' => Party::allForUser($user),
            'selectedPartyId' => $partyId,
            'selectedVendorId' => $vendorId,
            'vendors' => Vendor::all(),
            'errors' => [],
        ]);
    }

    public static function store(): void
    {
        $user = require_login();
        $partyId = post_int('party_id');
        $vendorId = post_int('vendor_id');
        $party = Party::find($partyId);
        $vendor = Vendor::find($vendorId);
        $errors = [];
        if (!$party || !Party::belongsTo($partyId, $user)) {
            $errors[] = 'Choose one of your parties.';
        } elseif ($party['party_date'] < date('Y-m-d')) {
            $errors[] = 'This party date has already passed, so it cannot take new bookings.';
        }
        if (!$vendor) {
            $errors[] = 'Choose an available vendor.';
        }
        if ($party && $vendor && Booking::existsForPartyAndVendor($partyId, $vendorId)) {
            $errors[] = 'This vendor is already booked for that party.';
        }
        if ($errors) {
            render('bookings/create', [
                'title' => 'Create a booking',
                'parties' => Party::allForUser($user),
                'selectedPartyId' => $partyId,
                'selectedVendorId' => $vendorId,
                'vendors' => Vendor::all(),
                'errors' => $errors,
            ]);
            return;
        }

        Booking::create($partyId, $vendorId, (float) $vendor['price']);
        flash('success', 'Your booking request has been sent.');
        redirect('bookings');
    }

    public static function edit(): void
    {
        $user = require_login();
        $booking = Booking::find(request_id());
        if (!$booking || ($user['role'] !== 'admin' && (int) $booking['parent_id'] !== (int) $user['user_id'])) {
            throw new HttpException(404, 'Booking not found.');
        }
        render('bookings/form', [
            'title' => 'Edit booking',
            'booking' => $booking,
            'parties' => Party::allForUser($user),
            'vendors' => Vendor::all(),
            'errors' => [],
        ]);
    }

    public static function update(): void
    {
        $user = require_login();
        $id = post_int('booking_id');
        $booking = Booking::find($id);
        if (!$booking || ($user['role'] !== 'admin' && (int) $booking['parent_id'] !== (int) $user['user_id'])) {
            throw new HttpException(404, 'Booking not found.');
        }

        $partyId = post_int('party_id');
        $vendorId = post_int('vendor_id');
        $party = Party::find($partyId);
        $vendor = Vendor::find($vendorId);
        $errors = [];
        if (!$party || !Party::belongsTo($partyId, $user)) {
            $errors[] = 'Choose one of your parties.';
        } elseif ($party['party_date'] < date('Y-m-d')) {
            $errors[] = 'This party date has already passed, so the booking cannot be moved to it.';
        }
        if (!$vendor) {
            $errors[] = 'Choose an available vendor.';
        }
        if ($party && $vendor && Booking::existsForPartyAndVendor($partyId, $vendorId, $id)) {
            $errors[] = 'This vendor is already booked for that party.';
        }

        $isAdmin = $user['role'] === 'admin';
        $status = $isAdmin ? post_string('status') : $booking['status'];
        if (!in_array($status, ['pending', 'confirmed', 'cancelled'], true)) {
            $errors[] = 'Choose a valid booking status.';
        }

        if ($isAdmin) {
            $amountInput = post_string('amount');
            $amount = whole_rwf_amount($amountInput);
            if ($amount === null) {
                $errors[] = 'Enter a whole RWF amount from 0 to 9,999,999.';
                $amount = (float) $booking['amount'];
            }
        } else {
            $amount = $vendor ? (float) $vendor['price'] : (float) $booking['amount'];
            if ($party && $vendor && (
                $partyId !== (int) $booking['party_id'] ||
                $vendorId !== (int) $booking['vendor_id']
            )) {
                $status = 'pending';
            }
        }

        if ($errors) {
            $booking['party_id'] = $partyId;
            $booking['vendor_id'] = $vendorId;
            $booking['amount'] = $amount;
            $booking['status'] = $status;
            render('bookings/form', [
                'title' => 'Edit booking',
                'booking' => $booking,
                'parties' => Party::allForUser($user),
                'vendors' => Vendor::all(),
                'errors' => $errors,
            ]);
            return;
        }

        Booking::update($id, $partyId, $vendorId, $amount, $status);
        flash('success', 'Booking updated.');
        redirect('bookings');
    }

    public static function status(): void
    {
        require_admin();
        $id = post_int('booking_id');
        $status = post_string('status');
        if (!Booking::find($id) || !in_array($status, ['pending', 'confirmed', 'cancelled'], true)) {
            throw new HttpException(404, 'Booking or status not found.');
        }
        Booking::updateStatus($id, $status);
        flash('success', 'Booking status updated.');
        redirect('bookings');
    }

    public static function delete(): void
    {
        $user = require_login();
        $id = post_int('booking_id');
        $booking = Booking::find($id);
        if (!$booking || ($user['role'] !== 'admin' && (int) $booking['parent_id'] !== (int) $user['user_id'])) {
            throw new HttpException(404, 'Booking not found.');
        }
        Booking::delete($id);
        flash('success', 'Booking removed.');
        redirect('bookings');
    }
}
