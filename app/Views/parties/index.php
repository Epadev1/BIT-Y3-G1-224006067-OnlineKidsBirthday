<section class="page-heading">
    <div><span class="eyebrow">YOUR PARTY PLANS</span><h1>My parties</h1><p>Keep all your party details in one place.</p></div>
    <a class="button" href="index.php?r=parties/create">＋ Plan a party</a>
</section>
<?php if (!$parties): ?>
    <div class="empty-panel"><span>🎉</span><h2>Your next celebration starts here</h2><p>Create a party plan to save your date, theme, and budget.</p><a class="button" href="index.php?r=parties/create">Create your first party</a></div>
<?php else: ?>
    <div class="table-wrap"><table>
        <thead><tr><th>Celebration</th><th>Date</th><th>Theme</th><th>Budget</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($parties as $party): ?>
            <tr>
                <td><a class="table-title-link" href="index.php?r=parties/view&id=<?= (int) $party['party_id'] ?>"><strong><?= e($party['child_name']) ?>’s party</strong></a><?php if (current_user()['role'] === 'admin'): ?><small class="cell-sub">Planned by <?= e($party['parent_name']) ?></small><?php endif; ?></td>
                <td><?= e(date('M j, Y', strtotime($party['party_date']))) ?></td>
                <td><span class="tag"><?= e($party['theme']) ?></span></td>
                <td><?= e(money_rwf($party['budget'])) ?></td>
                <td class="actions">
                    <a class="text-link" href="index.php?r=bookings/create&party_id=<?= (int) $party['party_id'] ?>">Book a vendor</a>
                    <a class="text-link" href="index.php?r=parties/edit&id=<?= (int) $party['party_id'] ?>">Edit</a>
                    <form method="post" action="index.php?r=parties/delete" data-confirm="Delete this party and its bookings?">
                        <?= csrf_field() ?><input type="hidden" name="party_id" value="<?= (int) $party['party_id'] ?>">
                        <button class="link-button danger-link" type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
<?php endif; ?>
