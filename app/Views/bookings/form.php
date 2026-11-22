<?php $isAdmin = current_user()['role'] === 'admin'; ?>
<section class="narrow-section">
    <a class="back-link" href="index.php?r=bookings">← Back to bookings</a>
    <div class="form-panel panel-wide">
        <span class="eyebrow">BOOKING DETAILS</span>
        <h1>Edit booking</h1>
        <p class="muted">Update the party or vendor details for this request.</p>
        <?php require __DIR__ . '/../components/errors.php'; ?>
        <form method="post" action="index.php?r=bookings/update" class="stack-form" data-client-validation>
            <?= csrf_field() ?>
            <input type="hidden" name="booking_id" value="<?= (int) $booking['booking_id'] ?>">
            <label>Party
                <select name="party_id" required>
                    <?php foreach ($parties as $party): ?>
                        <option value="<?= (int) $party['party_id'] ?>" <?= form_int_value('party_id', (int) $booking['party_id']) === (int) $party['party_id'] ? 'selected' : '' ?>>
                            <?= e($party['child_name']) ?>’s <?= e($party['theme']) ?> party · <?= e(date('M j, Y', strtotime($party['party_date']))) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Vendor
                <select name="vendor_id" required>
                    <?php foreach ($vendors as $vendor): ?>
                        <option value="<?= (int) $vendor['vendor_id'] ?>" <?= form_int_value('vendor_id', (int) $booking['vendor_id']) === (int) $vendor['vendor_id'] ? 'selected' : '' ?>>
                            <?= e($vendor['name']) ?> · <?= e($vendor['service_type']) ?> · <?= e(money_rwf($vendor['price'])) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <?php if ($isAdmin): ?>
                <div class="form-row">
                    <label>Booking amount (RWF)
                        <input type="number" name="amount" value="<?= e(form_value('amount', $booking['amount'])) ?>" min="0" max="9999999" step="1" required>
                    </label>
                    <label>Status
                        <select name="status" required>
                            <?php foreach (['pending', 'confirmed', 'cancelled'] as $status): ?>
                                <option value="<?= e($status) ?>" <?= form_value('status', $booking['status']) === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
            <?php else: ?>
                <p class="form-note">The booking amount will match the selected vendor’s listed price. Any change to the party or vendor sends the request back to pending.</p>
            <?php endif; ?>
            <button class="button" type="submit">Save booking</button>
        </form>
    </div>
</section>
