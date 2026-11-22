<?php $formData = $values ?? $vendor ?? []; $editing = !empty($vendor); ?>
<section class="narrow-section">
    <a class="back-link" href="index.php?r=vendors/manage">← Back to vendor management</a>
    <div class="form-panel panel-wide">
        <span class="eyebrow">VENDOR DIRECTORY</span><h1><?= $editing ? 'Edit vendor' : 'Add a vendor' ?></h1>
        <?php require __DIR__ . '/../components/errors.php'; ?>
        <form method="post" action="index.php?r=vendors/<?= $editing ? 'update' : 'store' ?>" class="stack-form" data-client-validation>
            <?= csrf_field() ?>
            <?php if ($editing): ?><input type="hidden" name="vendor_id" value="<?= (int) ($formData['vendor_id'] ?? 0) ?>"><?php endif; ?>
            <label>Business name<input type="text" name="name" value="<?= e(form_value('name', $formData['name'] ?? '')) ?>" required minlength="2" maxlength="120"></label>
            <label>Service type<select name="service_type" required>
                <option value="">Choose a service</option>
                <?php foreach (Vendor::SERVICE_TYPES as $type): ?>
                    <option value="<?= e($type) ?>" <?= form_value('service_type', $formData['service_type'] ?? '') === $type ? 'selected' : '' ?>><?= e($type) ?></option>
                <?php endforeach; ?>
            </select></label>
            <div class="form-row">
                <label>Starting price (RWF)<input type="number" name="price" value="<?= e(form_value('price', $formData['price'] ?? '')) ?>" required min="0" max="9999999" step="1"></label>
                <label>Contact email<input type="email" name="contact_email" value="<?= e(form_value('contact_email', $formData['contact_email'] ?? '')) ?>" required maxlength="190"></label>
            </div>
            <button class="button" type="submit"><?= $editing ? 'Save vendor' : 'Add vendor' ?></button>
        </form>
    </div>
</section>
