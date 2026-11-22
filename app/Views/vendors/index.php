<section class="page-heading">
    <div><span class="eyebrow">THE PEOPLE WHO MAKE IT SPECIAL</span><h1>Find your party people</h1><p>Search local favorites for every part of the celebration.</p></div>
    <?php if (current_user() && current_user()['role'] === 'admin'): ?>
        <a class="button" href="index.php?r=vendors/manage">Manage vendors <span aria-hidden="true">→</span></a>
    <?php elseif (current_user()): ?>
        <p class="form-note">Vendor listings are managed by an administrator.</p>
    <?php endif; ?>
</section>
<section class="vendor-search-section">
    <form class="search-form" method="get" action="index.php">
        <input type="hidden" name="r" value="vendors">
        <?php if ($service !== ''): ?><input type="hidden" name="service" value="<?= e($service) ?>"><?php endif; ?>
        <label class="search-box"><span aria-hidden="true">⌕</span><input id="vendor-search" name="q" type="search" value="<?= e($search) ?>" maxlength="80" placeholder="Try cakes, decorations, entertainment…" aria-label="Search vendors"><span id="search-status" class="sr-only" aria-live="polite"></span></label>
        <button class="button button-small" type="submit">Search</button>
    </form>
    <nav class="filter-chips" aria-label="Filter vendors by service type">
        <a class="chip<?= $service === '' ? ' is-active' : '' ?>" href="index.php?r=vendors<?= $search !== '' ? '&q=' . rawurlencode($search) : '' ?>"<?= $service === '' ? ' aria-current="page"' : '' ?>>All services</a>
        <?php foreach ($serviceTypes as $type): ?>
            <a class="chip<?= $service === $type ? ' is-active' : '' ?>" href="index.php?r=vendors&service=<?= rawurlencode($type) ?><?= $search !== '' ? '&q=' . rawurlencode($search) : '' ?>"<?= $service === $type ? ' aria-current="page"' : '' ?>><?= e($type) ?></a>
        <?php endforeach; ?>
    </nav>
    <div id="vendor-results" class="vendor-grid" data-api="index.php?r=api/vendors" data-service="<?= e($service) ?>" data-signed-in="<?= current_user() ? '1' : '0' ?>" data-admin="<?= current_user() && current_user()['role'] === 'admin' ? '1' : '0' ?>" data-csrf="<?= current_user() && current_user()['role'] === 'admin' ? e(csrf_token()) : '' ?>">
        <?php foreach ($vendors as $vendor): ?>
            <article class="vendor-card">
                <span class="vendor-icon"><?= $vendor['service_type'] === 'Cake' ? '🎂' : ($vendor['service_type'] === 'Decorations' ? '🎈' : ($vendor['service_type'] === 'Entertainment' ? '🎭' : '📸')) ?></span>
                <span class="tag"><?= e($vendor['service_type']) ?></span>
                <h2><?= e($vendor['name']) ?></h2>
                <p class="vendor-price">From <?= e(money_rwf($vendor['price'])) ?></p>
                <a class="vendor-contact" href="mailto:<?= e($vendor['contact_email']) ?>"><?= e($vendor['contact_email']) ?></a>
                <div class="vendor-card-actions">
                    <?php if (current_user()): ?>
                        <a class="text-link" href="index.php?r=bookings/create&vendor_id=<?= (int) $vendor['vendor_id'] ?>">Book this vendor <span aria-hidden="true">→</span></a>
                    <?php else: ?>
                        <a class="text-link" href="index.php?r=login">Sign in to book <span aria-hidden="true">→</span></a>
                    <?php endif; ?>
                </div>
                <?php if (current_user() && current_user()['role'] === 'admin'): ?>
                    <div class="admin-actions"><a class="text-link" href="index.php?r=vendors/edit&id=<?= (int) $vendor['vendor_id'] ?>">Edit</a>
                        <form method="post" action="index.php?r=vendors/delete" data-confirm="Delete this vendor? Existing bookings will also be removed.">
                            <?= csrf_field() ?><input type="hidden" name="vendor_id" value="<?= (int) $vendor['vendor_id'] ?>">
                            <button class="link-button danger-link" type="submit">Delete</button>
                        </form>
                    </div>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
    <?php if (!$vendors): ?><div class="empty-state" id="vendor-empty">No vendors found. Try a different search.</div><?php else: ?><div class="empty-state" id="vendor-empty" hidden>No vendors found. Try a different search.</div><?php endif; ?>
    <noscript><p class="muted">Live search needs JavaScript. You can still browse all vendors above.</p></noscript>
</section>
