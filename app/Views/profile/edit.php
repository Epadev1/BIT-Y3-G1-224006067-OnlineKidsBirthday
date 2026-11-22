<?php $formData = $values ?? $person ?? []; ?>
<section class="narrow-section">
    <a class="back-link" href="index.php?r=home">← Back home</a>
    <div class="form-panel panel-wide">
        <span class="eyebrow">YOUR ACCOUNT</span>
        <h1>Your profile</h1>
        <p class="muted">Update your details or choose a new password. Leave the password fields blank to keep your current password.</p>
        <?php require __DIR__ . '/../components/errors.php'; ?>
        <form method="post" action="index.php?r=profile/update" class="stack-form" data-client-validation>
            <?= csrf_field() ?>
            <label>Your name
                <input type="text" name="username" value="<?= e(form_value('username', $formData['username'] ?? '')) ?>" required minlength="2" maxlength="80" autocomplete="name">
            </label>
            <label>Email address
                <input type="email" name="email" value="<?= e(form_value('email', $formData['email'] ?? '')) ?>" required maxlength="190" autocomplete="email">
            </label>
            <div class="form-divider"><span>Change password (optional)</span></div>
            <label>Current password
                <input type="password" name="current_password" minlength="8" autocomplete="current-password">
            </label>
            <div class="form-row">
                <label>New password
                    <input type="password" name="new_password" minlength="8" maxlength="72" autocomplete="new-password">
                </label>
                <label>Confirm new password
                    <input type="password" name="confirm_password" minlength="8" maxlength="72" autocomplete="new-password">
                </label>
            </div>
            <p class="form-note">Passwords must be 8 to 72 bytes. Changing your password signs out any other browser using the old one.</p>
            <button class="button" type="submit">Save profile</button>
        </form>
    </div>
</section>
