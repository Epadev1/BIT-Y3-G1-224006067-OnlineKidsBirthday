<section class="auth-shell">
    <div class="auth-decoration"><span>✦</span><h2>Your next<br>great day<br>starts here.</h2><p>All your party details, in one happy place.</p></div>
    <div class="form-panel">
        <span class="eyebrow">WELCOME BACK</span><h1>Sign in</h1><p class="muted">Pick up where the party planning left off.</p>
        <?php require __DIR__ . '/../components/errors.php'; ?>
        <form method="post" action="index.php?r=auth/login" class="stack-form" data-client-validation>
            <?= csrf_field() ?>
            <label>Email address<input type="email" name="email" value="<?= e($email ?? '') ?>" required autocomplete="email"></label>
            <label>Password<input type="password" name="password" required autocomplete="current-password"></label>
            <button class="button button-full" type="submit">Sign in</button>
        </form>
        <p class="form-foot">New to Kids Birthday? <a href="index.php?r=register">Create an account</a></p>
    </div>
</section>
