<?php include __DIR__ . '/header.php'; include __DIR__ . '/master_helpers.php'; ?>
<main class="gm-slip-page gm-master-page"><div class="gm-slip-inner">
    <header class="gm-slip-heading gm-master-heading">
        <div><span class="gm-slip-eyebrow">Master data</span><div class="gm-slip-title-row"><h1><?= $masterTitle ?></h1></div><p>Manage the records used throughout your documents.</p></div>
        <a class="gm-slip-submit gm-master-link" href="<?= $masterEscape($masterUrl('master_create')) ?>">+ Create new</a>
    </header>
    <section class="gm-slip-section" aria-label="<?= $masterTitle ?> records">
        <div class="gm-slip-section-heading">
            <div><h2>All <?= strtolower($masterTitle) ?></h2><p id="masterRecordCount" aria-live="polite"><?= count($result) ?> records</p></div>
            <div class="gm-master-search"><label for="masterSearch">Search records</label><input id="masterSearch" type="search" placeholder="Search by code, name, or details" autocomplete="off"></div>
        </div>
        <div class="gm-slip-table-scroll"><table class="gm-master-table" id="masterTable">
            <thead><tr><?php foreach ($keyNames as $name): ?><th scope="col"><?= $masterEscape($masterFieldLabel($name)) ?></th><?php endforeach; ?><th scope="col">Actions</th></tr></thead>
            <tbody>
                <?php foreach ($result as $record):
                    $recordCode = $data === 'users' ? $record['userID'] : $record[$keyNames[0]];
                    $protected = $data === 'users' && strcasecmp(trim($record['username']), 'admin1') === 0;
                ?>
                <tr>
                    <?php foreach ($keyNames as $name): ?><td data-master-value><?= $masterEscape($record[$name] ?? '') ?></td><?php endforeach; ?>
                    <td><div class="gm-master-row-actions">
                        <?php if ($protected): ?><span class="gm-slip-mode">Protected account</span><?php else: ?>
                        <a class="gm-master-edit" href="<?= $masterEscape($masterUrl('master_update', $recordCode)) ?>" aria-label="Edit <?= $masterEscape($recordCode) ?>">Edit</a>
                        <a class="gm-slip-remove gm-master-link" href="<?= $masterEscape($masterUrl('master_delete', $recordCode)) ?>" aria-label="Delete <?= $masterEscape($recordCode) ?>">Delete</a>
                        <?php endif; ?>
                    </div></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table></div>
        <p class="gm-slip-empty" id="masterEmpty"<?= count($result) ? ' hidden' : '' ?>>No records yet. Use <strong>+ Create new</strong> to begin.</p>
        <p class="gm-slip-table-hint">Scroll horizontally to see all details and actions.</p>
    </section>
</div></main>
<script src="../js/master_ui.js" defer></script>
<?php include __DIR__ . '/footer.php'; ?>
