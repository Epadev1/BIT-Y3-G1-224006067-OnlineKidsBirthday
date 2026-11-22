<?php $formData = $values ?? $party ?? []; $editing = !empty($party); ?>
<section class="narrow-section">
    <a class="back-link" href="index.php?r=parties">← Back to my parties</a>
    <div class="form-panel panel-wide">
        <span class="eyebrow"><?= $editing ? 'TWEAK THE DETAILS' : 'LET’S GET THE PARTY STARTED' ?></span>
        <h1><?= $editing ? 'Edit your party' : 'Plan a party' ?></h1>
        <p class="muted">A few details now make the big day easier later.</p>
        <?php require __DIR__ . '/../components/errors.php'; ?>
        <form method="post" action="index.php?r=parties/<?= $editing ? 'update' : 'store' ?>" class="stack-form" data-client-validation>
            <?= csrf_field() ?>
            <?php if ($editing): ?><input type="hidden" name="party_id" value="<?= (int) ($formData['party_id'] ?? 0) ?>"><?php endif; ?>
            <label>Birthday child’s name<input type="text" name="child_name" value="<?= e(form_value('child_name', $formData['child_name'] ?? '')) ?>" required maxlength="80"></label>
            <div class="form-row">
                <label>Party date<input type="date" name="party_date" value="<?= e(form_value('party_date', $formData['party_date'] ?? '')) ?>" required min="<?= e(date('Y-m-d')) ?>"></label>
                <label>Budget (RWF)<input type="number" name="budget" value="<?= e(form_value('budget', $formData['budget'] ?? '')) ?>" required min="0" max="9999999" step="1"></label>
            </div>
            <label>Party theme<input type="text" name="theme" value="<?= e(form_value('theme', $formData['theme'] ?? '')) ?>" required minlength="2" maxlength="100" placeholder="e.g. Under the sea"></label>
            <button class="button" type="submit"><?= $editing ? 'Save changes' : 'Save party plan' ?></button>
        </form>
    </div>
</section>
