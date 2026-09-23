<?php
// Both entry points use this layout, while retaining their own submit action.
$slipDirection = $slipAmend ? substr($pageState, strlen('amend_slip_')) : $pageState;
$slipIncoming = $slipDirection === 'in';
$slipLabel = ['in' => 'Slip in', 'out' => 'Slip out', 'out_tax' => 'Slip tax out'][$slipDirection];
$slipGenerator = ['in' => 'getLPB()', 'out' => 'getSJ()', 'out_tax' => 'getSJT()'][$slipDirection];
$slipData = $slipAmend ? $result : [];
$slipProducts = $slipAmend ? $products : [];
$slipEscape = static fn($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$slipValue = static fn($key, $fallback = '') => $slipData[$key] ?? $fallback;
?>
<main class="gm-slip-page">
    <div class="gm-slip-inner">
        <header class="gm-slip-heading">
            <span class="gm-slip-eyebrow"><?php echo $slipAmend ? 'Amend record' : 'New document'; ?></span>
            <div class="gm-slip-title-row">
                <h1><?php echo $slipAmend ? 'Amend ' . strtolower($slipLabel) : $slipLabel; ?></h1>
                <span class="gm-slip-mode"><?php echo $slipDirection === 'in' ? 'Incoming' : ($slipDirection === 'out_tax' ? 'Outgoing · Tax' : 'Outgoing'); ?></span>
            </div>
            <p><?php echo $slipAmend ? 'Update the document details and materials for this slip.' : 'Enter the document details, then add the materials for this shipment.'; ?></p>
            <?php if ($slipAmend): ?>
            <p class="gm-slip-reference">Editing <strong><?php echo $slipEscape($slipValue('nomor_surat_jalan')); ?></strong></p>
            <?php endif; ?>
        </header>

        <form id="myForm" action="<?php echo $slipEscape($slipFormAction); ?>" method="post">
            <input type="hidden" id="pageState" name="pageState" value="<?php echo $slipEscape($pageState); ?>">
            <?php if ($slipAmend): ?>
            <input type="hidden" id="old_sj" name="old_sj" value="<?php echo $slipEscape($slipValue('nomor_surat_jalan')); ?>">
            <?php endif; ?>

            <section class="gm-slip-section" aria-labelledby="slipDetailsHeading">
                <div class="gm-slip-section-heading">
                    <div><h2 id="slipDetailsHeading">Slip details</h2><p>Fields marked * are required.</p></div>
                </div>
                <div class="gm-slip-fields">
                    <div class="gm-slip-field">
                        <label for="storageCode">PT</label>
                        <?php if ($slipDirection === 'out'): ?>
                        <select name="storageCode" id="storageCode"><option value="NON" selected>none</option></select>
                        <?php else: ?>
                        <select name="storageCode" id="storageCode" onchange="<?php echo $slipGenerator; ?>">
                            <?php foreach (getAllStorages() as $storage): ?>
                            <option value="<?php echo $slipEscape($storage['storageCode']); ?>"<?php echo $storage['storageCode'] == $slipValue('storageCode', 'NON') ? ' selected' : ''; ?>><?php echo $slipEscape($storage['storageName']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php endif; ?>
                    </div>
                    <div class="gm-slip-field">
                        <?php if ($slipIncoming): ?>
                        <label for="vendorCode">Vendor</label>
                        <select name="vendorCode" id="vendorCode">
                            <?php foreach (getAllVendors() as $vendor): ?>
                            <option value="<?php echo $slipEscape($vendor['vendorCode']); ?>"<?php echo $vendor['vendorCode'] == $slipValue('vendorCode', 'NON') ? ' selected' : ''; ?>><?php echo $slipEscape($vendor['vendorName']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php else: ?>
                        <label for="customerCode">Customer</label>
                        <select name="customerCode" id="customerCode">
                            <?php foreach (getAllCustomers() as $customer): ?>
                            <option value="<?php echo $slipEscape($customer['customerCode']); ?>"<?php echo $customer['customerCode'] == $slipValue('customerCode', 'NON') ? ' selected' : ''; ?>><?php echo $slipEscape($customer['customerName']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php endif; ?>
                    </div>
                    <div class="gm-slip-field">
                        <?php if ($slipIncoming): ?>
                        <label for="no_lpb_display">No. LPB <span class="gm-slip-auto">Automatic</span></label>
                        <input name="no_lpb_display" id="no_lpb_display" type="text" value="<?php echo $slipEscape($slipValue('no_LPB')); ?>" placeholder="Otomatis dari sistem" readonly>
                        <input name="no_LPB" id="no_LPB" type="hidden" value="<?php echo $slipEscape($slipValue('no_LPB')); ?>">
                        <?php else: ?>
                        <label for="no_sj">No. SJ <span class="gm-slip-auto">Automatic</span></label>
                        <input name="no_sj" id="no_sj" type="text" value="<?php echo $slipEscape($slipValue('nomor_surat_jalan')); ?>" placeholder="Otomatis dari sistem" readonly>
                        <?php endif; ?>
                    </div>
                    <div class="gm-slip-field">
                        <label for="tgl_penerimaan">Tgl Penerimaan *</label>
                        <input name="order_date" id="tgl_penerimaan" type="date" value="<?php echo $slipEscape($slipValue('order_date')); ?>" onchange="<?php echo $slipGenerator; ?>" required>
                    </div>
                    <?php if ($slipIncoming): ?>
                    <div class="gm-slip-field">
                        <label for="no_sj">No. SJ *</label>
                        <input name="no_sj" id="no_sj" type="text" value="<?php echo $slipEscape($slipValue('nomor_surat_jalan')); ?>" placeholder="di isi" required>
                    </div>
                    <?php else: ?>
                    <div class="gm-slip-field">
                        <label for="no_truk">No. Truk *</label>
                        <input name="no_truk" id="no_truk" type="text" value="<?php echo $slipEscape($slipValue('no_truk')); ?>" placeholder="di isi" required>
                    </div>
                    <?php endif; ?>
                    <div class="gm-slip-field">
                        <label for="purchase_order">No. PO *</label>
                        <input name="purchase_order" id="purchase_order" type="text" value="<?php echo $slipEscape($slipValue('purchase_order')); ?>" placeholder="di isi" required>
                    </div>
                    <?php if ($slipIncoming): ?>
                    <div class="gm-slip-field">
                        <label for="no_truk">No. Truk *</label>
                        <input name="no_truk" id="no_truk" type="text" value="<?php echo $slipEscape($slipValue('no_truk')); ?>" placeholder="di isi" required>
                    </div>
                    <?php endif; ?>
                </div>
            </section>

            <section class="gm-slip-section gm-slip-materials" aria-labelledby="slipMaterialsHeading">
                <div class="gm-slip-section-heading">
                    <div><h2 id="slipMaterialsHeading">Materials <span id="slipRowCount" class="gm-slip-row-count"></span></h2><p>Select a product code to fill its material name.</p></div>
                    <button type="button" class="gm-slip-add" onclick="addRow()">+ Add row</button>
                </div>
                <div class="gm-slip-table-scroll">
                    <table id="productTable">
                        <thead><tr><th scope="col">No</th><th scope="col">KD *</th><th scope="col">Material</th><th scope="col">QTY *</th><th scope="col">UOM *</th><th scope="col">Note</th><th scope="col">Action</th></tr></thead>
                        <tbody>
                            <?php $slipRowNumber = 0; foreach ($slipProducts as $product): ?>
                            <tr>
                                <td><?php echo ++$slipRowNumber; ?></td>
                                <td><input type="text" name="kd[]" class="productCode" oninput="applyAutocomplete(this)" value="<?php echo $slipEscape($product['productCode']); ?>" placeholder="Kode Produk" required></td>
                                <td><input type="text" name="material_display[]" value="<?php echo $slipEscape($product['productName']); ?>" readonly><input type="hidden" name="material[]" value="<?php echo $slipEscape($product['productName']); ?>"></td>
                                <td><input type="number" name="qty[]" value="<?php echo $slipEscape($product['qty']); ?>" placeholder="0" required></td>
                                <td><input type="text" name="uom[]" value="<?php echo $slipEscape($product['uom']); ?>" placeholder="UOM" required></td>
                                <td><input type="text" name="note[]" value="<?php echo $slipEscape($product['note'] ?? ''); ?>" placeholder="Optional"></td>
                                <td><button type="button" class="gm-slip-remove" onclick="deleteRow(this)">Remove</button></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p id="slipEmptyState" class="gm-slip-empty"<?php echo count($slipProducts) ? ' hidden' : ''; ?>>No materials added yet. Use <strong>+ Add row</strong> to begin.</p>
                <p class="gm-slip-table-hint">Scroll horizontally to see all item fields on smaller screens.</p>
            </section>
            <div class="gm-slip-actions">
                <p>Review the document details and quantities before saving.</p>
                <button type="submit" class="gm-slip-submit"><?php echo $slipAmend ? 'Save changes' : 'Create slip'; ?></button>
            </div>
        </form>
    </div>
</main>
<script src="../js/index.js" defer></script>
