<?php include "header.php"; include "master_helpers.php"; ?>
<main class="gm-form-page gm-master-page"><div class="gm-form-inner">
    <header class="gm-form-heading gm-master-heading">
        <div><span class="gm-form-eyebrow">Master data</span><div class="gm-form-title-row"><h1><?= $masterTitle ?></h1></div></div>
        <a class="gm-form-submit gm-master-link" href="<?= $masterEscape($masterUrl('master_create')) ?>">+ Create new</a>
    </header>
    <section class="gm-form-section" aria-label="<?= $masterTitle ?> records">
        <div class="gm-form-section-heading">
            <div><h2>All <?= strtolower($masterTitle) ?></h2><p id="masterRecordCount" aria-live="polite"><?= count($result) ?> records</p></div>
            <div class="gm-master-search"><label for="masterSearch">Search records</label><input id="masterSearch" type="search" placeholder="Search by code, name, or details" autocomplete="off"></div>
        </div>
        <div class="gm-form-table-scroll"><table class="gm-master-table" id="masterTable">
            <thead><tr><?php foreach ($keyNames as $name): ?><th scope="col"><?= $masterEscape($masterFieldLabel($name)) ?></th><?php endforeach; ?><th scope="col">Actions</th></tr></thead>
            <tbody>
                <?php foreach ($result as $record):
                    $recordCode = $data === 'users' ? $record['userID'] : $record[$keyNames[0]];
                    $protected = $data === 'users' && strcasecmp(trim($record['username']), 'admin1') === 0;
                ?>
                <tr>
                    <?php foreach ($keyNames as $name): ?><td data-master-value><?= $masterEscape($record[$name] ?? '') ?></td><?php endforeach; ?>
                    <td><div class="gm-master-row-actions">
                        <?php if ($protected): ?><span class="gm-form-mode">Protected account</span><?php else: ?>
                        <a class="gm-master-edit" href="<?= $masterEscape($masterUrl('master_update', $recordCode)) ?>" aria-label="Edit <?= $masterEscape($recordCode) ?>">Edit</a>
                        <a class="gm-form-remove gm-master-link" href="<?= $masterEscape($masterUrl('master_delete', $recordCode)) ?>" aria-label="Delete <?= $masterEscape($recordCode) ?>">Delete</a>
                        <?php endif; ?>
                    </div></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table></div>
        <p class="gm-form-empty" id="masterEmpty"<?= count($result) ? ' hidden' : '' ?>>No records yet. Use <strong>+ Create new</strong> to begin.</p>
        <p class="gm-form-table-hint">Scroll horizontally to see all details and actions.</p>
    </section>
</div></main>
<script src="../js/master_ui.js" defer></script>
<?php include "footer.php"; ?>
