<?php
$movingEscape = static fn($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$movingData = $movingAmend ? $result : [];
$movingValue = static fn($key, $fallback = '') => $movingData[$key] ?? $fallback;
$movingRows = $movingAmend ? $products : [['productCode' => '', 'productName' => '', 'qty' => '', 'uom' => '', 'price_per_UOM' => '']];
$movingStorages = getAllStorages();
?>
<main class="gm-slip-page gm-moving-page">
    <div class="gm-slip-inner">
        <header class="gm-slip-heading">
            <span class="gm-slip-eyebrow"><?= $movingAmend ? 'Amend record' : 'New document' ?></span>
            <div class="gm-slip-title-row"><h1><?= $movingAmend ? 'Amend moving barang' : 'Slip moving barang' ?></h1><span class="gm-slip-mode">Transfer</span></div>
            <p>Select the sending and receiving PT, then add the materials to transfer.</p>
            <?php if ($movingAmend): ?>
            <p class="gm-slip-reference">Editing <strong><?= $movingEscape($movingValue('no_moving')) ?></strong></p>
            <?php endif; ?>
        </header>
        <form id="myForm" action="<?= $movingEscape($movingFormAction) ?>" method="post">
            <input type="hidden" id="pageState" name="pageState" value="<?= $movingEscape($pageState) ?>">
            <?php if ($movingAmend): ?>
            <input type="hidden" id="old_moving" name="old_moving" value="<?= $movingEscape($movingValue('no_moving')) ?>">
            <?php endif; ?>
            <section class="gm-slip-section" aria-labelledby="movingDetailsHeading">
                <div class="gm-slip-section-heading"><div><h2 id="movingDetailsHeading">Transfer details</h2><p>Fields marked * are required.</p></div></div>
                <div class="gm-slip-fields">
                    <?php foreach (['Sender' => 'PT Pengirim', 'Receiver' => 'PT Penerima'] as $side => $label): ?>
                    <div class="gm-slip-field gm-moving-party">
                        <label for="storageCode<?= $side ?>"><?= $label ?> <span class="gm-slip-auto"><?= $side === 'Sender' ? 'From' : 'To' ?></span></label>
                        <select name="storageCode<?= $side ?>" id="storageCode<?= $side ?>"<?= $side === 'Sender' ? ' onchange="getMovingNO()"' : '' ?>>
                            <?php foreach ($movingStorages as $storage): ?>
                            <option value="<?= $movingEscape($storage['storageCode']) ?>"<?= $storage['storageCode'] == $movingValue('storageCode' . $side, 'NON') ? ' selected' : '' ?>><?= $movingEscape($storage['storageName']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endforeach; ?>
                    <div class="gm-slip-field"><label for="no_moving">No. Moving <span class="gm-slip-auto">Automatic</span></label><input name="no_moving" id="no_moving" type="text" value="<?= $movingEscape($movingValue('no_moving')) ?>" placeholder="Otomatis dari sistem" readonly></div>
                    <div class="gm-slip-field"><label for="moving_date">Tgl. Moving *</label><input name="moving_date" id="moving_date" onchange="getMovingNO()" type="date" value="<?= $movingEscape($movingValue('moving_date')) ?>" required></div>
                </div>
            </section>
            <section class="gm-slip-section" aria-labelledby="movingMaterialsHeading">
                <div class="gm-slip-section-heading">
                    <div><h2 id="movingMaterialsHeading">Materials <span class="gm-slip-row-count" id="movingRowCount"></span></h2><p>Unit prices are calculated from the sending PT and transfer date.</p></div>
                    <button type="button" class="gm-slip-add" onclick="addRow('materialTable')">+ Add row</button>
                </div>
                <div class="gm-slip-table-scroll">
                    <table id="materialTable" class="gm-moving-table">
                        <thead><tr><th scope="col">No</th><th scope="col">KD *</th><th scope="col">Material</th><th scope="col">QTY *</th><th scope="col">UOM *</th><th scope="col">Price / UOM</th><th scope="col">Nominal</th><th scope="col">Action</th></tr></thead>
                        <tbody>
                            <?php $movingRowNumber = 0; foreach ($movingRows as $product): ?>
                            <tr>
                                <td><?= ++$movingRowNumber ?></td>
                                <td><input name="kd[]" class="productCode" oninput="applyAutocomplete(this)" type="text" placeholder="Product code" value="<?= $movingEscape($product['productCode']) ?>" required></td>
                                <td><input name="productName[]" type="text" placeholder="Terisi otomatis" value="<?= $movingEscape($product['productName']) ?>" readonly></td>
                                <td><input name="qty[]" type="text" placeholder="0" oninput="calculateNominal(this)" value="<?= $movingEscape($product['qty']) ?>" required></td>
                                <td><input name="uom[]" type="text" placeholder="UOM" value="<?= $movingEscape($product['uom']) ?>" required></td>
                                <td><input name="price_per_uom[]" type="text" placeholder="Otomatis" value="<?= $movingEscape($product['price_per_UOM']) ?>" readonly></td>
                                <td><input name="nominal[]" type="number" inputmode="numeric" placeholder="Otomatis" value="<?= $movingAmend ? $movingEscape((float) $product['qty'] * (float) $product['price_per_UOM']) : '' ?>" readonly></td>
                                <td><button type="button" class="gm-slip-remove" onclick="removeRow(this)">Remove</button></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p id="movingEmptyState" class="gm-slip-empty" hidden>No materials added. Use <strong>+ Add row</strong> to begin.</p>
                <p class="gm-slip-table-hint">Scroll horizontally to see prices, amounts, and row actions.</p>
            </section>
            <div class="gm-slip-actions"><p>Review the sending PT, receiving PT, and quantities before saving.</p><button type="submit" class="gm-slip-submit"><?= $movingAmend ? 'Save changes' : 'Create transfer' ?></button></div>
        </form>
    </div>
</main>
<script src="../js/moving.js" defer></script>
