<?php $formData = $values ?? $person ?? []; $editing = !empty($person); ?>
<section class="narrow-section">
    <a class="back-link" href="index.php?r=users">← Back to users</a>
    <div class="form-panel panel-wide">
        <span class="eyebrow">ADMINISTRATION</span><h1><?= $editing ? 'Edit user' : 'Add a user' ?></h1>
        <?php require __DIR__ . '/../components/errors.php'; ?>
        <form method="post" action="index.php?r=users/<?= $editing ? 'update' : 'store' ?>" class="stack-form" data-client-validation>
            <?= csrf_field() ?>
            <?php if ($editing): ?><input type="hidden" name="user_id" value="<?= (int) ($formData['user_id'] ?? 0) ?>"><?php endif; ?>
            <label>Name<input type="text" name="username" value="<?= e(form_value('username', $formData['username'] ?? '')) ?>" required minlength="2" maxlength="80"></label>
            <label>Email<input type="email" name="email" value="<?= e(form_value('email', $formData['email'] ?? '')) ?>" required maxlength="190"></label>
            <label>Password<?= $editing ? ' (leave blank to keep current)' : '' ?><input type="password" name="password" <?= $editing ? '' : 'required' ?> minlength="8" maxlength="72" autocomplete="new-password"><small>Use 8 to 72 bytes.</small></label>
            <label>Role<select name="role" required>
                <?php foreach (['parent', 'admin'] as $role): ?>
                    <option value="<?= e($role) ?>" <?= form_value('role', $formData['role'] ?? 'parent') === $role ? 'selected' : '' ?>><?= e(ucfirst($role)) ?></option>
                <?php endforeach; ?>
            </select></label>
            <button class="button" type="submit"><?= $editing ? 'Save user' : 'Create user' ?></button>
        </form>
    </div>
</section>
