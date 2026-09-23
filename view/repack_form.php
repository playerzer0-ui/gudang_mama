<?php
$repackEscape = static fn($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$repackData = $repackAmend ? $result : [];
$repackValue = static fn($key, $fallback = '') => $repackData[$key] ?? $fallback;
?>
<main class="gm-slip-page gm-repack-page">
    <div class="gm-slip-inner">
        <header class="gm-slip-heading">
            <span class="gm-slip-eyebrow"><?= $repackAmend ? 'Amend record' : 'New document' ?></span>
            <div class="gm-slip-title-row"><h1><?= $repackAmend ? 'Amend repack barang' : 'Slip repack barang' ?></h1><span class="gm-slip-mode">Repack</span></div>
            <p>Enter the original materials and the new materials produced by this repack.</p>
            <?php if ($repackAmend): ?>
            <p class="gm-slip-reference">Editing <strong><?= $repackEscape($repackValue('no_repack')) ?></strong></p>
            <?php endif; ?>
        </header>
        <form id="myForm" action="<?= $repackEscape($repackFormAction) ?>" method="post">
            <input type="hidden" id="pageState" name="pageState" value="<?= $repackEscape($pageState) ?>">
            <?php if ($repackAmend): ?>
            <input type="hidden" id="old_rpeack" name="old_repack" value="<?= $repackEscape($repackValue('no_repack')) ?>">
            <?php endif; ?>
            <section class="gm-slip-section" aria-labelledby="repackDetailsHeading">
                <div class="gm-slip-section-heading"><div><h2 id="repackDetailsHeading">Repack details</h2><p>Fields marked * are required.</p></div></div>
                <div class="gm-slip-fields">
                    <div class="gm-slip-field">
                        <label for="storageCode">PT</label>
                        <select name="storageCode" id="storageCode" onchange="getRepackNO()">
                            <?php foreach (getAllStorages() as $storage): ?>
                            <option value="<?= $repackEscape($storage['storageCode']) ?>"<?= $storage['storageCode'] == $repackValue('storageCode', 'NON') ? ' selected' : '' ?>><?= $repackEscape($storage['storageName']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="gm-slip-field"><label for="repack_date">Tgl Repack *</label><input name="repack_date" id="repack_date" onchange="getRepackNO()" type="date" value="<?= $repackEscape($repackValue('repack_date')) ?>" required></div>
                    <div class="gm-slip-field"><label for="no_repack">No. Repack <span class="gm-slip-auto">Automatic</span></label><input name="no_repack" id="no_repack" type="text" value="<?= $repackEscape($repackValue('no_repack')) ?>" placeholder="Otomatis dari sistem" readonly></div>
                </div>
            </section>
            <?php foreach ([['awal', 'materialAwalTable', 'Material Awal', 'Original materials used for this repack.'], ['akhir', 'materialBaruTable', 'Material Baru', 'New materials produced by this repack.']] as [$suffix, $tableId, $heading, $description]):
                $repackRows = $repackAmend ? array_filter($products, static fn($product) => $product['product_status'] === 'repack_' . $suffix) : [['productCode' => '', 'productName' => '', 'qty' => '', 'uom' => '', 'note' => '']];
                $repackRowNumber = 0;
            ?>
            <section class="gm-slip-section gm-repack-materials" aria-labelledby="<?= $tableId ?>Heading">
                <div class="gm-slip-section-heading">
                    <div><h2 id="<?= $tableId ?>Heading"><?= $heading ?> <span class="gm-slip-row-count" id="<?= $tableId ?>Count"></span></h2><p><?= $description ?></p></div>
                    <button type="button" class="gm-slip-add" onclick="addRow('<?= $tableId ?>')" aria-label="Add row to <?= $heading ?>">+ Add row</button>
                </div>
                <div class="gm-slip-table-scroll">
                    <table id="<?= $tableId ?>" class="gm-repack-table">
                        <thead><tr><th scope="col">No</th><th scope="col">KD *</th><th scope="col"><?= $heading ?></th><th scope="col">QTY *</th><th scope="col">UOM *</th><th scope="col">Note</th><th scope="col">Action</th></tr></thead>
                        <tbody>
                            <?php foreach ($repackRows as $product): ?>
                            <tr>
                                <td><?= ++$repackRowNumber ?></td>
                                <td><input name="kd_<?= $suffix ?>[]" class="productCode" oninput="applyAutocomplete(this)" type="text" placeholder="Product code" value="<?= $repackEscape($product['productCode']) ?>" required></td>
                                <td><input name="material_<?= $suffix ?>[]" type="text" placeholder="Terisi otomatis" value="<?= $repackEscape($product['productName']) ?>" readonly></td>
                                <td><input name="qty_<?= $suffix ?>[]" type="text" placeholder="0" value="<?= $repackEscape($product['qty']) ?>" required></td>
                                <td><input name="uom_<?= $suffix ?>[]" type="text" placeholder="UOM" value="<?= $repackEscape($product['uom']) ?>" required></td>
                                <td><input name="note_<?= $suffix ?>[]" type="text" placeholder="Optional" value="<?= $repackEscape($product['note'] ?? '') ?>"></td>
                                <td><button type="button" class="gm-slip-remove" onclick="removeRow(this)">Remove</button></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p id="<?= $tableId ?>Empty" class="gm-slip-empty" hidden>No materials added. Use <strong>+ Add row</strong> to begin.</p>
                <p class="gm-slip-table-hint">Scroll horizontally to see all item fields on smaller screens.</p>
            </section>
            <?php endforeach; ?>
            <div class="gm-slip-actions"><p>Review both material lists and quantities before saving.</p><button type="submit" class="gm-slip-submit"><?= $repackAmend ? 'Save changes' : 'Create repack' ?></button></div>
        </form>
    </div>
</main>
<script src="../js/repack.js" defer></script>
