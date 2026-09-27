<?php
include __DIR__ . '/header.php';
$data = 'users';
include __DIR__ . '/master_helpers.php';
$masterEdit = $action !== 'master_create';
$accountName = $masterEdit ? $result['username'] : '';
?>
<main class="gm-form-page gm-master-page"><div class="gm-master-form-inner">
    <header class="gm-form-heading"><span class="gm-form-eyebrow">Master data · Users</span><div class="gm-form-title-row"><h1><?= $masterEdit ? 'Edit user' : 'Create user' ?></h1></div></header>
    <form action="<?= $masterEscape($masterUrl($masterEdit ? 'master_update_data' : 'master_create_data')) ?>" method="post">
        <input type="hidden" name="data" value="users">
        <input type="hidden" name="oldCode" value="<?= $masterEscape($accountName) ?>">
        <section class="gm-form-section"><div class="gm-form-section-heading"><div><h2>Account details</h2><p>Fields marked * are required.</p></div></div><div class="gm-form-fields">
            <div class="gm-form-field"><label for="masterUsername">Username *</label><input type="text" name="input_data[]" id="masterUsername" value="<?= $masterEscape($accountName) ?>" required></div>
            <div class="gm-form-field"><label for="masterPassword"><?= $masterEdit ? 'New password' : 'Password' ?> *</label><input type="password" name="input_data[]" id="masterPassword" autocomplete="new-password" required></div>
            <div class="gm-form-field"><label for="masterUserType">User type *</label><select name="input_data[]" id="masterUserType" required><option value="0"<?= !$masterEdit || (int) $result['userType'] === 0 ? ' selected' : '' ?>>Normal user</option><option value="1"<?= $masterEdit && (int) $result['userType'] === 1 ? ' selected' : '' ?>>Admin user</option></select></div>
        </div></section>
        <div class="gm-form-actions"><a class="gm-master-edit" href="<?= $masterEscape($masterUrl('master_read')) ?>">Back to users</a><button type="submit" class="gm-form-submit"><?= $masterEdit ? 'Save changes' : 'Create user' ?></button></div>
    </form>
</div></main>
<?php include __DIR__ . '/footer.php'; ?>
