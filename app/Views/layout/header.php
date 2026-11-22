<?php $flashes = take_flashes(); ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Plan a joyful kids' birthday with trusted local party vendors.">
    <meta name="theme-color" content="#16324f">
    <title><?= e($title ?? 'Kids Birthday') ?> · Kids Birthday</title>
    <link rel="icon" href="assets/images/birthday-mark.svg" type="image/svg+xml">
    <link rel="stylesheet" href="assets/css/style.css?v=2">
</head>
<body>
<a class="skip-link" href="#main-content">Skip to content</a>
<header class="site-header">
    <div class="nav-wrap">
        <a class="brand" href="index.php?r=home" aria-label="Kids Birthday home">
            <img class="brand-mark" src="assets/images/birthday-mark.svg" alt="">
            <span class="brand-copy"><span class="brand-name">Kids Birthday</span><span class="brand-caption">PARTY PLANNER</span></span>
        </a>
        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="main-navigation" aria-label="Toggle navigation">Menu</button>
        <nav class="main-nav" id="main-navigation" aria-label="Main navigation">
            <a href="index.php?r=home">Home</a>
            <a href="index.php?r=vendors">Vendors</a>
            <?php if (current_user()): ?>
                <a href="index.php?r=parties">My parties</a>
                <a href="index.php?r=bookings">Bookings</a>
                <?php if (current_user()['role'] === 'admin'): ?>
                    <a href="index.php?r=vendors/manage">Manage vendors</a>
                <?php endif; ?>
                <a class="nav-user" href="index.php?r=profile" title="Your profile">Hi, <?= e(current_user()['role'] === 'admin' ? 'Admin' : current_user()['username']) ?></a>
                <form class="nav-logout" method="post" action="index.php?r=logout">
                    <?= csrf_field() ?>
                    <button class="link-button" type="submit">Sign out</button>
                </form>
            <?php else: ?>
                <a href="index.php?r=login">Sign in</a>
                <a class="button button-small" href="index.php?r=register">Get started</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="main-content" id="main-content">
    <?php foreach ($flashes as $flash): ?>
        <div class="flash flash-<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></div>
    <?php endforeach; ?>
