<section class="auth-shell">
    <div class="auth-decoration auth-decoration-warm"><span>🎈</span><h2>Make room<br>for more<br>magic.</h2><p>Plan the party. Keep the happy.</p></div>
    <div class="form-panel">
        <span class="eyebrow">LET’S MAKE A DAY</span><h1>Create an account</h1><p class="muted">It only takes a moment to get started.</p>
        <?php require __DIR__ . '/../components/errors.php'; ?>
        <form method="post" action="index.php?r=auth/register" class="stack-form" data-client-validation>
            <?= csrf_field() ?>
            <label>Your name<input type="text" name="username" value="<?= old_value('username') ?>" required minlength="2" maxlength="80" autocomplete="name"></label>
            <label>Email address<input type="email" name="email" value="<?= old_value('email') ?>" required autocomplete="email"></label>
            <label>Password<input type="password" name="password" required minlength="8" maxlength="72" autocomplete="new-password"><small>Use 8 to 72 bytes.</small></label>
            <button class="button button-full" type="submit">Create my account</button>
        </form>
        <p class="form-foot">Already have an account? <a href="index.php?r=login">Sign in</a></p>
    </div>
</section>
