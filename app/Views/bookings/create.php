<?php $backLink = !empty($selectedPartyId) ? 'index.php?r=parties/view&id=' . (int) $selectedPartyId : 'index.php?r=parties'; ?>
<section class="narrow-section">
    <a class="back-link" href="<?= e($backLink) ?>">← Back to <?= !empty($selectedPartyId) ? 'party' : 'my parties' ?></a>
    <div class="form-panel panel-wide">
        <span class="eyebrow">MAKE IT OFFICIAL</span><h1>Create a booking</h1>
        <p class="muted">Choose one of your party plans and a vendor to send a booking request.</p>
        <?php require __DIR__ . '/../components/errors.php'; ?>
        <?php if (!$parties): ?>
            <div class="empty-state">Create a party plan before requesting a vendor booking.<p><a class="button" href="index.php?r=parties/create">Create a party</a></p></div>
        <?php elseif (!$vendors): ?>
            <div class="empty-state">There are no vendors available right now. Please check back soon.</div>
        <?php else: ?>
            <form method="post" action="index.php?r=bookings/store" class="stack-form" data-client-validation>
                <?= csrf_field() ?>
                <label>Party<select name="party_id" required>
                    <option value="">Choose a party</option>
                    <?php foreach ($parties as $party): ?>
                        <option value="<?= (int) $party['party_id'] ?>" <?= (int) ($selectedPartyId ?? 0) === (int) $party['party_id'] ? 'selected' : '' ?>>
                            <?= e($party['child_name']) ?>’s <?= e($party['theme']) ?> party · <?= e(date('M j, Y', strtotime($party['party_date']))) ?>
                        </option>
                    <?php endforeach; ?>
                </select></label>
                <label>Choose a vendor<select name="vendor_id" required>
                    <option value="">Select a vendor</option>
                    <?php foreach ($vendors as $vendor): ?>
                        <option value="<?= (int) $vendor['vendor_id'] ?>" <?= (int) ($selectedVendorId ?? 0) === (int) $vendor['vendor_id'] ? 'selected' : '' ?>><?= e($vendor['name']) ?> · <?= e($vendor['service_type']) ?> · <?= e(money_rwf($vendor['price'])) ?></option>
                    <?php endforeach; ?>
                </select></label>
                <p class="form-note">Your request starts as pending. The vendor’s listed price is saved as the booking amount.</p>
                <button class="button" type="submit">Request booking</button>
            </form>
        <?php endif; ?>
    </div>
</section>
