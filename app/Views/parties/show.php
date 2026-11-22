<?php $isAdmin = current_user()['role'] === 'admin'; ?>
<section class="page-heading">
    <div>
        <a class="back-link" href="index.php?r=parties">← Back to my parties</a>
        <span class="eyebrow">PARTY PLAN</span>
        <h1><?= e($party['child_name']) ?>’s <?= e($party['theme']) ?> party</h1>
        <p><?= e(date('l, M j, Y', strtotime($party['party_date']))) ?><?php if ($isAdmin): ?> · Planned by <?= e($party['parent_name'] ?? 'a parent') ?><?php endif; ?></p>
    </div>
    <div class="page-actions">
        <a class="button" href="index.php?r=bookings/create&party_id=<?= (int) $party['party_id'] ?>">＋ Book a vendor</a>
        <a class="button button-outline" href="index.php?r=parties/edit&id=<?= (int) $party['party_id'] ?>">Edit party</a>
    </div>
</section>

<section class="summary-grid" aria-label="Party budget summary">
    <article class="summary-card">
        <span class="summary-label">Budget</span>
        <strong class="summary-value"><?= e(money_rwf($party['budget'])) ?></strong>
    </article>
    <article class="summary-card">
        <span class="summary-label">Booked so far</span>
        <strong class="summary-value"><?= e(money_rwf($booked)) ?></strong>
    </article>
    <article class="summary-card<?= $remaining < 0 ? ' is-negative' : '' ?>">
        <span class="summary-label"><?= $remaining < 0 ? 'Over budget by' : 'Left to spend' ?></span>
        <strong class="summary-value"><?= e(money_rwf(abs($remaining))) ?></strong>
    </article>
</section>

<section class="section-block">
    <div class="section-heading section-heading-tight">
        <div><span class="eyebrow">THE PEOPLE MAKING IT HAPPEN</span><h2>Vendor bookings</h2></div>
        <span class="record-count"><?= count($bookings) ?> <?= count($bookings) === 1 ? 'booking' : 'bookings' ?></span>
    </div>
    <?php if (!$bookings): ?>
        <div class="empty-panel">
            <span>🎈</span>
            <h2>No vendors booked yet</h2>
            <p>Browse the directory and send your first booking request for this party.</p>
            <a class="button" href="index.php?r=bookings/create&party_id=<?= (int) $party['party_id'] ?>">Book a vendor</a>
        </div>
    <?php else: ?>
        <div class="table-wrap"><table>
            <thead><tr><th>Vendor</th><th>Service</th><th>Amount</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($bookings as $booking): ?>
                <tr>
                    <td><strong><?= e($booking['vendor_name']) ?></strong></td>
                    <td><?= e($booking['service_type']) ?></td>
                    <td><?= e(money_rwf($booking['amount'])) ?></td>
                    <td><span class="status status-<?= e($booking['status']) ?>"><?= e(ucfirst($booking['status'])) ?></span></td>
                    <td class="actions">
                        <a class="text-link" href="index.php?r=bookings/edit&id=<?= (int) $booking['booking_id'] ?>">Edit</a>
                        <form method="post" action="index.php?r=bookings/delete" data-confirm="Remove this booking?">
                            <?= csrf_field() ?><input type="hidden" name="booking_id" value="<?= (int) $booking['booking_id'] ?>">
                            <button class="link-button danger-link" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
    <?php endif; ?>
</section>
