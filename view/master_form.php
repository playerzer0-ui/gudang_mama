<?php include __DIR__ . '/master_helpers.php'; ?>
<main class="gm-form-page gm-master-page"><div class="gm-master-form-inner">
    <header class="gm-form-heading"><span class="gm-form-eyebrow">Master data · <?= $masterTitle ?></span><div class="gm-form-title-row"><h1><?= $masterEdit ? 'Edit record' : 'Create record' ?></h1></div></header>
    <form action="<?= $masterEscape($masterUrl($masterEdit ? 'master_update_data' : 'master_create_data')) ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="data" value="<?= $masterEscape($data) ?>">
        <?php if ($masterEdit): ?><input type="hidden" name="oldCode" value="<?= $masterEscape($result[$keyNames[0]]) ?>"><?php endif; ?>
        <section class="gm-form-section"><div class="gm-form-section-heading"><h2>Record details</h2></div><div class="gm-form-fields">
            <?php foreach ($keyNames as $index => $name): ?>
            <div class="gm-form-field"><label for="masterField<?= $index ?>"><?= $masterEscape($masterFieldLabel($name)) ?></label><input id="masterField<?= $index ?>" type="text" name="input_data[]" value="<?= $masterEdit ? $masterEscape($result[$name] ?? '') : '' ?>"></div>
            <?php endforeach; ?>
            <?php if ($data === 'storage'): ?>
            <div class="gm-form-field gm-master-wide"><label for="masterLogo">Logo file</label><input type="file" name="logo" id="masterLogo" accept=".png,.jpg,.jpeg,.gif,.webp"><p class="gm-master-help">Upload a logo when saving this storage record.</p></div>
            <?php endif; ?>
        </div></section>
        <div class="gm-form-actions"><a class="gm-master-edit" href="<?= $masterEscape($masterUrl('master_read')) ?>">Back to <?= strtolower($masterTitle) ?></a><button type="submit" class="gm-form-submit"><?= $masterEdit ? 'Save changes' : 'Create record' ?></button></div>
    </form>
</div></main>
