<section class="page-heading">
    <div>
        <span class="eyebrow">ADMINISTRATION</span>
        <h1>Manage vendors</h1>
        <p>Add, update, and remove businesses from the public directory.</p>
    </div>
    <a class="button" href="index.php?r=vendors/create">＋ Add vendor</a>
</section>

<div class="manage-toolbar">
    <a class="back-link" href="index.php?r=vendors">← Back to vendor directory</a>
    <span class="record-count"><?= count($vendors) ?> <?= count($vendors) === 1 ? 'vendor' : 'vendors' ?></span>
</div>

<?php if (!$vendors): ?>
    <div class="empty-panel">
        <span>🎂</span>
        <h2>No vendors yet</h2>
        <p>Add the first party professional to the directory.</p>
        <a class="button" href="index.php?r=vendors/create">Add a vendor</a>
    </div>
<?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Business</th><th>Service</th><th>Starting price</th><th>Contact</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($vendors as $vendor): ?>
                    <tr>
                        <td><strong><?= e($vendor['name']) ?></strong></td>
                        <td><span class="tag"><?= e($vendor['service_type']) ?></span></td>
                        <td><?= e(money_rwf($vendor['price'])) ?></td>
                        <td><a class="vendor-contact" href="mailto:<?= e($vendor['contact_email']) ?>"><?= e($vendor['contact_email']) ?></a></td>
                        <td class="actions">
                            <a class="text-link" href="index.php?r=vendors/edit&id=<?= (int) $vendor['vendor_id'] ?>">Edit</a>
                            <form method="post" action="index.php?r=vendors/delete" data-confirm="Delete this vendor? Existing bookings will also be removed.">
                                <?= csrf_field() ?>
                                <input type="hidden" name="vendor_id" value="<?= (int) $vendor['vendor_id'] ?>">
                                <button class="link-button danger-link" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
