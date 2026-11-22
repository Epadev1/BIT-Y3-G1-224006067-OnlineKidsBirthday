<?php
declare(strict_types=1);

final class PartyController
{
    public static function index(): void
    {
        $user = require_login();
        render('parties/index', ['title' => 'My parties', 'parties' => Party::allForUser($user)]);
    }

    public static function show(): void
    {
        $user = require_login();
        $party = Party::find(request_id());
        if (!$party || !Party::belongsTo((int) $party['party_id'], $user)) {
            throw new HttpException(404, 'Party not found.');
        }
        $bookings = Booking::forParty((int) $party['party_id']);
        $booked = array_sum(array_map(static fn(array $booking): float => (float) $booking['amount'], $bookings));
        render('parties/show', [
            'title' => $party['child_name'] . '’s party',
            'party' => $party,
            'bookings' => $bookings,
            'booked' => $booked,
            'remaining' => (float) $party['budget'] - $booked,
        ]);
    }

    public static function create(): void
    {
        require_login();
        render('parties/form', ['title' => 'Create a party', 'party' => null, 'errors' => []]);
    }

    public static function store(): void
    {
        $user = require_login();
        [$values, $errors] = self::validatedInput();
        if ($errors) {
            render('parties/form', ['title' => 'Create a party', 'party' => null, 'errors' => $errors, 'values' => $values]);
            return;
        }
        Party::create((int) $user['user_id'], $values['child_name'], $values['party_date'], $values['theme'], $values['budget']);
        flash('success', 'Party created. Start choosing vendors!');
        redirect('parties');
    }

    public static function edit(): void
    {
        $user = require_login();
        $party = Party::find(request_id());
        if (!$party || !Party::belongsTo((int) $party['party_id'], $user)) {
            throw new HttpException(404, 'Party not found.');
        }
        render('parties/form', ['title' => 'Edit party', 'party' => $party, 'errors' => []]);
    }

    public static function update(): void
    {
        $user = require_login();
        $id = post_int('party_id');
        if (!Party::belongsTo($id, $user)) {
            throw new HttpException(404, 'Party not found.');
        }
        [$values, $errors] = self::validatedInput();
        if ($errors) {
            $values['party_id'] = $id;
            render('parties/form', ['title' => 'Edit party', 'party' => $values, 'errors' => $errors, 'values' => $values]);
            return;
        }
        Party::update($id, $values['child_name'], $values['party_date'], $values['theme'], $values['budget']);
        flash('success', 'Party details updated.');
        redirect('parties');
    }

    public static function delete(): void
    {
        $user = require_login();
        $id = post_int('party_id');
        if (!Party::belongsTo($id, $user)) {
            throw new HttpException(404, 'Party not found.');
        }
        Party::delete($id);
        flash('success', 'Party and its bookings were deleted.');
        redirect('parties');
    }

    private static function validatedInput(): array
    {
        $values = [
            'child_name' => post_string('child_name'),
            'party_date' => post_string('party_date'),
            'theme' => post_string('theme'),
            'budget' => post_string('budget'),
        ];
        $errors = [];
        if (strlen($values['child_name']) < 1 || strlen($values['child_name']) > 80) {
            $errors[] = 'Child name is required and must be 80 characters or fewer.';
        }
        $date = DateTime::createFromFormat('Y-m-d', $values['party_date']);
        if (!$date || $date->format('Y-m-d') !== $values['party_date']) {
            $errors[] = 'Choose a valid party date.';
        } elseif ($values['party_date'] < date('Y-m-d')) {
            $errors[] = 'Party date cannot be in the past.';
        }
        if (strlen($values['theme']) < 2 || strlen($values['theme']) > 100) {
            $errors[] = 'Theme must be between 2 and 100 characters.';
        }
        $budget = whole_rwf_amount($values['budget']);
        if ($budget === null) {
            $errors[] = 'Enter a whole RWF budget from 0 to 9,999,999.';
        } else {
            $values['budget'] = $budget;
        }
        return [$values, $errors];
    }
}
