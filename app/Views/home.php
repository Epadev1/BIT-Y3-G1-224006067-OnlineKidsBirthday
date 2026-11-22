<section class="hero">
    <div class="hero-copy">
        <?php if (current_user()): ?>
            <p class="hero-welcome">Welcome back, <strong><?= e(current_user()['role'] === 'admin' ? 'Admin' : current_user()['username']) ?></strong> <span aria-hidden="true">🎈</span></p>
        <?php endif; ?>
        <span class="eyebrow">THE BIG DAY, MADE EASY</span>
        <h1>Kids’ birthdays.<br><span>Big magic.</span></h1>
        <p>One happy place to plan a celebration they’ll talk about all year. Find your theme, choose your people, and enjoy the day.</p>
        <div class="hero-actions">
            <?php if (current_user()): ?>
                <a class="button" href="index.php?r=parties/create">Plan a party <span aria-hidden="true">→</span></a>
            <?php else: ?>
                <a class="button" href="index.php?r=register">Start planning <span aria-hidden="true">→</span></a>
            <?php endif; ?>
            <a class="button button-outline" href="index.php?r=vendors">Explore vendors</a>
        </div>
        <div class="mini-stats" aria-label="Planner totals">
            <span><strong><?= (int) $vendorCount ?></strong> vendors</span>
            <span><strong><?= (int) $partyCount ?></strong> parties</span>
            <span><strong><?= (int) $bookingCount ?></strong> bookings</span>
        </div>
        <?php if (current_user()): ?>
            <?php if (!empty($nextParty)): ?>
                <p class="hero-next">Next up: <a href="index.php?r=parties/view&id=<?= (int) $nextParty['party_id'] ?>"><strong><?= e($nextParty['child_name']) ?>’s <?= e($nextParty['theme']) ?> party</strong></a> · <?= e(date('M j, Y', strtotime($nextParty['party_date']))) ?></p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <div class="hero-art">
        <img class="party-illustration" src="assets/images/family-party.svg" alt="A parent and two children celebrating together around a birthday cake">
        <span class="hero-art-note" aria-hidden="true">Big moments, happy memories</span>
    </div>
</section>

<section class="section section-soft">
    <div class="section-heading">
        <div><span class="eyebrow">HELP FOR EVERY KID'S BIRTHDAY</span><h2>Party planning, <span class="heading-accent">made joyful.</span></h2></div>
        <a class="text-link" href="index.php?r=vendors">Meet the vendors <span>→</span></a>
    </div>
    <div class="feature-grid">
        <article class="feature-card"><span class="feature-icon icon-peach">01</span><h3>Dream it up</h3><p>Create a party plan with your date, theme, and budget all in one place.</p></article>
        <article class="feature-card"><span class="feature-icon icon-lilac">02</span><h3>Find your favorites</h3><p>Browse cake makers, decorators, entertainers, and more.</p></article>
        <article class="feature-card"><span class="feature-icon icon-mint">03</span><h3>Enjoy the day</h3><p>Keep bookings organized so you can focus on the smiles.</p></article>
    </div>
</section>

<section class="section vendor-preview">
    <div class="section-heading">
        <div><span class="eyebrow">GOOD PEOPLE, GREAT PARTIES</span><h2>Meet a few party pros</h2></div>
        <a class="text-link" href="index.php?r=vendors">See all vendors <span>→</span></a>
    </div>
    <?php if ($vendors): ?>
        <div class="vendor-grid">
            <?php foreach ($vendors as $vendor): ?>
                <article class="vendor-card">
                    <span class="vendor-icon"><?= $vendor['service_type'] === 'Cake' ? '🎂' : ($vendor['service_type'] === 'Decorations' ? '🎈' : ($vendor['service_type'] === 'Entertainment' ? '🎭' : '📸')) ?></span>
                    <span class="tag"><?= e($vendor['service_type']) ?></span>
                    <h3><?= e($vendor['name']) ?></h3>
                    <p class="vendor-price">From <?= e(money_rwf($vendor['price'])) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="empty-state">Vendor listings are coming soon.</p>
    <?php endif; ?>
</section>
