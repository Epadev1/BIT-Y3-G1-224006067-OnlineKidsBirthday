<section class="page-heading">
    <div><span class="eyebrow">THE FUN IS COMING TOGETHER</span><h1>Bookings</h1><p>Keep track of the wonderful people helping with your celebration.</p></div>
    <a class="button" href="index.php?r=bookings/create">＋ New booking</a>
</section>
<?php if (!$bookings): ?>
    <div class="empty-panel"><span>✨</span><h2>No bookings just yet</h2><p>Once you’ve made a party plan, book a vendor to get the details rolling.</p><a class="button" href="index.php?r=bookings/create">Create a booking</a></div>
<?php else: ?>
    <div class="table-wrap"><table>
        <thead><tr><th>Vendor</th><th>Service</th><th>Party</th><th>Date</th><th>Amount</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($bookings as $booking): ?>
            <tr>
                <td><strong><?= e($booking['vendor_name']) ?></strong></td><td><?= e($booking['service_type']) ?></td>
                <td><?= e($booking['child_name']) ?>’s party<?php if (current_user()['role'] === 'admin'): ?><small class="cell-sub">Planned by <?= e($booking['parent_name']) ?></small><?php endif; ?></td><td><?= e(date('M j, Y', strtotime($booking['party_date']))) ?></td>
                <td><?= e(money_rwf($booking['amount'])) ?></td><td><span class="status status-<?= e($booking['status']) ?>"><?= e(ucfirst($booking['status'])) ?></span></td>
                <td class="actions">
                    <a class="text-link" href="index.php?r=bookings/edit&id=<?= (int) $booking['booking_id'] ?>">Edit</a>
                    <?php if (current_user()['role'] === 'admin'): ?>
                        <form class="inline-form" method="post" action="index.php?r=bookings/status">
                            <?= csrf_field() ?><input type="hidden" name="booking_id" value="<?= (int) $booking['booking_id'] ?>">
                            <label class="sr-only" for="status-<?= (int) $booking['booking_id'] ?>">Booking status</label>
                            <select id="status-<?= (int) $booking['booking_id'] ?>" name="status" aria-label="Booking status">
                                <?php foreach (['pending', 'confirmed', 'cancelled'] as $status): ?>
                                    <option value="<?= e($status) ?>" <?= $booking['status'] === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option>
                                <?php endforeach; ?>
                            </select><button class="link-button" type="submit">Save</button>
                        </form>
                    <?php endif; ?>
                    <form method="post" action="index.php?r=bookings/delete" data-confirm="Remove this booking?">
                        <?= csrf_field() ?><input type="hidden" name="booking_id" value="<?= (int) $booking['booking_id'] ?>"><button class="link-button danger-link" type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
<?php endif; ?>
