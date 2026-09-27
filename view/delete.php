<?php include "header.php"; ?>

<?php $masterDelete = in_array($data, ['vendor', 'customer', 'product', 'storage', 'users'], true); ?>
<main<?= $masterDelete ? ' class="gm-form-page gm-master-page"' : '' ?>>
    <?php if ($masterDelete): include __DIR__ . '/master_helpers.php'; ?>
        <div class="gm-master-form-inner">
            <header class="gm-form-heading"><span class="gm-form-eyebrow">Master data · <?= $masterTitle ?></span>
                <div class="gm-form-title-row">
                    <h1>Delete record</h1>
                </div>
                <p>Confirm deletion of <strong><?= $masterEscape($code) ?></strong>.</p>
            </header>
            <section class="gm-form-section">
                <div class="gm-master-delete-copy">
                <?php endif; ?>
                <?php if (!$masterDelete): ?><h1>CONFIRM DELETE <span style="color: red;"><?php echo strtoupper($code); ?></span>?</h1><?php endif; ?>
                <?php if ($data == "slip" || $data == "invoice" || $data == "payment" || $data == "repack" || $data == "moving") { ?>
                    <form action="../controller/index.php?action=amend_delete_data" method="post">
                    <?php } else { ?>
                        <form action="../controller/index.php?action=master_delete_data" method="post">
                        <?php } ?>
                        <input type="hidden" name="data" value="<?php echo htmlspecialchars($data); ?>">
                        <input type="hidden" name="code" value="<?php echo htmlspecialchars($code); ?>">
                        <?php if ($data == "slip") { ?>
                            <p>You are about to delete a slip, and by default all records linked to the slip will be gone (invoices, payments), are you sure you want to delete this slip?</p>
                        <?php } else if ($data == "invoice" || $data == "payment" || $data == "repack" || $data == "moving") { ?>
                            <p>You are about to delete a record, the record deleted might affect other records, are you sure you want to delete this record?</p>
                        <?php } else { ?>
                            <p>this data resource will no longer exist on the master table, if there are any orders linked to this data, it won't delete and send an error instead</p>
                        <?php } ?>
                        <?php if ($masterDelete): ?><div class="gm-form-actions"><a class="gm-master-edit" href="<?= $masterEscape($masterUrl('master_read')) ?>">Cancel</a><button type="submit" class="gm-form-remove">Delete record</button></div><?php else: ?><button type="submit" class="btn btn-danger">DELETE FOREVER</button><?php endif; ?>
                        </form>
                        <?php if ($masterDelete): ?>
                </div>
            </section>
        </div><?php endif; ?>
    </main>

    <?php include "footer.php"; ?>