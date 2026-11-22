<section class="page-heading">
    <div><span class="eyebrow">ADMINISTRATION</span><h1>Manage users</h1><p>Review and update Kids Birthday accounts.</p></div>
    <a class="button" href="index.php?r=users/create">＋ Add user</a>
</section>
<div class="table-wrap"><table>
    <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Joined</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($users as $person): ?>
        <tr>
            <td><strong><?= e($person['username']) ?></strong></td><td><?= e($person['email']) ?></td>
            <td><span class="tag"><?= e($person['role']) ?></span></td><td><?= e(date('M j, Y', strtotime($person['created_at']))) ?></td>
            <td class="actions"><a class="text-link" href="index.php?r=users/edit&id=<?= (int) $person['user_id'] ?>">Edit</a>
                <?php if ((int) $person['user_id'] !== (int) current_user()['user_id']): ?>
                    <form method="post" action="index.php?r=users/delete" data-confirm="Delete this user and all their parties and bookings?">
                        <?= csrf_field() ?><input type="hidden" name="user_id" value="<?= (int) $person['user_id'] ?>"><button class="link-button danger-link" type="submit">Delete</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table></div>
