<?php
// Create and amend invoice. $pageState is in / out / out_tax / moving, or amend_invoice_<mode>.
// Amend pages also receive $result, $invoice and $products from amend_controller.php.
require_once "partials/form_field.php";

$isAmend = strpos($pageState, 'amend_invoice_') === 0;
$mode = $isAmend ? substr($pageState, strlen('amend_invoice_')) : $pageState;
$hasDocumentSidebar = !$isAmend && in_array($mode, ['in', 'out', 'out_tax'], true);
$escape = static fn($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
// Existing values are only shown when amending.
$resultValue = static fn($key) => $isAmend ? $result[$key] : null;
$invoiceValue = static fn($key) => $isAmend ? $invoice[$key] : null;

$auto = 'Otomatis dari sistem';
$sjLookup = $isAmend ? null : 'getDetailsFromSJ()';
if ($mode === 'moving') {
    $fields = [
        ['storageCodeSender', 'PT Pengirim', ['placeholder' => 'otomatis', 'value' => $resultValue('storageCodeSender'), 'readonly' => true]],
        ['storageCodeReceiver', 'PT Penerima', ['placeholder' => 'otomatis', 'value' => $resultValue('storageCodeReceiver'), 'readonly' => true]],
        $isAmend
            ? ['no_moving', 'NO. moving', ['value' => $resultValue('no_moving'), 'readonly' => true]]
            : ['no_moving', 'NO. moving', ['oninput' => 'getMovingDetailsFromMovingNo()', 'required' => true]],
        ['moving_date', 'Tgl. moving', ['type' => 'date', 'value' => $resultValue('moving_date'), 'readonly' => true]],
        ['no_invoice', 'No Invoice', ['placeholder' => 'otomatis dari sistem', 'value' => $invoiceValue('no_invoice'), 'readonly' => true]],
        ['invoice_date', 'Tgl invoice', ['type' => 'date', 'placeholder' => 'di isi', 'value' => $invoiceValue('invoice_date'), 'oninput' => 'generateNoInvoice()', 'required' => true]],
    ];
} elseif ($mode === 'in') {
    $fields = [
        ['storageCode', 'PT', ['placeholder' => $auto, 'value' => $resultValue('storageCode'), 'readonly' => true]],
        ['vendorCode', 'Name Vendor', ['placeholder' => $auto, 'value' => $resultValue('vendorCode'), 'readonly' => true]],
        ['no_LPB', 'NO. LPB', ['placeholder' => $auto, 'value' => $resultValue('no_LPB'), 'readonly' => true]],
        ['purchase_order', 'No PO', ['placeholder' => $auto, 'value' => $resultValue('purchase_order'), 'readonly' => true]],
        ['no_sj', 'No SJ', ['placeholder' => 'di isi', 'value' => $resultValue('nomor_surat_jalan'), 'oninput' => $sjLookup, 'required' => true]],
        ['invoice_date', 'Tgl invoice', ['type' => 'date', 'placeholder' => 'di isi', 'value' => $invoiceValue('invoice_date'), 'required' => true]],
        ['no_truk', 'No Truk', ['placeholder' => $auto, 'value' => $resultValue('no_truk'), 'readonly' => true]],
        ['no_invoice', 'No Invoice', ['placeholder' => 'di isi', 'value' => $invoiceValue('no_invoice'), 'required' => true]],
    ];
} else {
    $fields = [
        ['storageCode', 'PT', ['placeholder' => $auto, 'value' => $resultValue('storageCode'), 'readonly' => true]],
        ['customerCode', 'Name Customer', ['placeholder' => $auto, 'value' => $resultValue('customerCode'), 'readonly' => true]],
        ['no_sj', 'No SJ', ['placeholder' => 'di isi', 'value' => $resultValue('nomor_surat_jalan'), 'oninput' => $sjLookup, 'required' => true]],
        ['customerAddress', 'Alamat', ['placeholder' => $auto, 'value' => $resultValue('customerAddress'), 'readonly' => true]],
        ['no_invoice', 'No Invoice', ['placeholder' => 'otomatis dari sistem', 'value' => $invoiceValue('no_invoice'), 'readonly' => true]],
        ['npwp', 'NPWP', ['placeholder' => $auto, 'value' => $resultValue('customerNPWP'), 'readonly' => true]],
        ['invoice_date', 'Tgl invoice', ['type' => 'date', 'placeholder' => 'di isi', 'value' => $invoiceValue('invoice_date'), 'oninput' => 'generateNoInvoice()', 'required' => true]],
    ];
}

$totalNominal = 0;
if ($isAmend) {
    foreach ($products as $product) {
        $totalNominal += (int) $product['qty'] * (float) $product['price_per_UOM'];
    }
    $taxPPN = $totalNominal * ($invoice['tax'] / 100);
}
?>
<?php include "header.php"; ?>

<main class="gm-form-page gm-invoice-page">
    <div class="gm-invoice-inner">
        <?php if ($hasDocumentSidebar) { $sidebarPurpose = 'invoice'; include "source_sidebar.php"; } ?>
        <section class="document-form">
            <?php if ($isAmend) { ?>
            <form id="myForm" action="../controller/index.php?action=amend_update_data&data=invoice" method="post">
            <?php } else { ?>
            <form id="myForm" action="../controller/index.php?action=create_invoice" target="_blank" method="post">
            <?php } ?>
                <header class="gm-form-heading">
                    <span class="gm-form-eyebrow"><?php echo $isAmend ? 'Amend record' : 'New document'; ?></span>
                    <div class="gm-form-title-row">
                        <h1><?php echo $isAmend ? 'Amend invoice' : 'Invoice'; ?></h1>
                        <span class="gm-form-mode"><?php echo $escape(str_replace('_', ' ', $mode)); ?></span>
                    </div>
                    <?php if ($isAmend) { ?>
                    <p class="gm-form-reference">Editing <strong><?php echo $escape($invoice['no_invoice']); ?></strong></p>
                    <?php } ?>
                </header>
                <input type="hidden" id="pageState" name="pageState" value="<?php echo $escape($pageState); ?>">
                <?php if ($isAmend) { ?>
                <input name="old_invoice" type="hidden" id="old_invoice" placeholder="otomatis dari sistem" value="<?php echo $escape($invoice['no_invoice']); ?>">
                <?php if ($mode !== 'moving') { ?>
                <input name="old_sj" type="hidden" id="old_sj" value="<?php echo $escape($result['nomor_surat_jalan']); ?>">
                <?php } ?>
                <?php } ?>

                <section class="gm-form-section">
                    <div class="gm-form-section-heading">
                        <div>
                            <h2>Invoice details</h2>
                            <p>Fields marked * are required.</p>
                        </div>
                    </div>
                    <div class="gm-form-fields">
                        <?php foreach ($fields as [$id, $label, $options]) form_field($id, $label, $options); ?>
                    </div>
                </section>

                <section class="gm-form-section">
                    <div class="gm-form-section-heading">
                        <div>
                            <h2>Materials <span id="invoiceRowCount" class="gm-form-row-count"></span></h2>
                            <p>Quantities come from the source document. Enter the price per unit.</p>
                        </div>
                    </div>
                    <div class="gm-form-table-scroll">
                        <table id="productTable">
                            <thead>
                                <tr>
                                    <th scope="col">No</th>
                                    <th scope="col">KD</th>
                                    <th scope="col">Material</th>
                                    <th scope="col">QTY</th>
                                    <th scope="col">UOM</th>
                                    <th scope="col">Price / UOM *</th>
                                    <th scope="col">Nominal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- New invoices get their rows from invoice.js -->
                                <?php if ($isAmend) foreach ($products as $index => $product) { ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><input type="text" name="kd[]" value="<?php echo $escape($product['productCode']); ?>" class="productCode" readonly></td>
                                    <td><input value="<?php echo $escape($product['productName']); ?>" type="text" name="material_display[]" readonly><input type="hidden" value="<?php echo $escape($product['productName']); ?>" name="material[]"></td>
                                    <td><input type="number" value="<?php echo $escape($product['qty']); ?>" name="qty[]" readonly></td>
                                    <td><input type="text" value="<?php echo $escape($product['uom']); ?>" name="uom[]" readonly></td>
                                    <td><input type="number" value="<?php echo $escape($product['price_per_UOM']); ?>" inputmode="numeric" name="price_per_uom[]" placeholder="di isi" oninput="calculateNominal(this)" required></td>
                                    <td><input type="text" name="nominal[]" placeholder="otomatis dari sistem" value="<?php echo (int) $product['qty'] * (float) $product['price_per_UOM']; ?>" readonly></td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <p id="invoiceEmptyState" class="gm-form-empty" hidden>Select a source document to load its materials.</p>
                    <p class="gm-form-table-hint">Scroll horizontally to see prices and amounts.</p>
                </section>

                <section id="accountTable" class="gm-form-section gm-invoice-account">
                    <div class="gm-form-section-heading">
                        <h2>Invoice total</h2>
                    </div>
                    <div class="gm-invoice-summary">
                        <?php form_field('no_faktur', 'No. Faktur', ['placeholder' => 'di isi', 'value' => $invoiceValue('no_faktur'), 'required' => true]); ?>
                        <div class="gm-invoice-totals">
                            <div class="gm-invoice-total-row"><label for="totalNominal">Total Nilai Barang</label><input type="number" inputmode="numeric" name="totalNominal" id="totalNominal"<?php if ($isAmend) echo ' value="' . $totalNominal . '"'; ?> disabled></div>
                            <div class="gm-invoice-total-row"><label for="tax">PPN (%)</label><input type="number" name="tax" id="tax" value="<?php echo $isAmend ? $escape($invoice['tax']) : 11; ?>" oninput="calculateTotalNominal()"></div>
                            <div class="gm-invoice-total-row"><label for="taxPPN">Nilai PPN</label><input type="number" inputmode="numeric" name="taxPPN" id="taxPPN"<?php if ($isAmend) echo ' value="' . $taxPPN . '"'; ?> disabled></div>
                            <div class="gm-invoice-total-row"><label for="amount_paid">Nilai Dibayar</label><input type="number" inputmode="numeric" name="amount_paid" id="amount_paid"<?php if ($isAmend) echo ' value="' . ($taxPPN + $totalNominal) . '"'; ?> disabled></div>
                        </div>
                    </div>
                </section>

                <div class="gm-form-actions">
                    <p>Review prices and tax before saving.</p>
                    <div class="gm-invoice-buttons">
                        <?php if ($isAmend) { ?>
                        <button type="submit" class="gm-form-submit">Save changes</button>
                        <?php $pdfReference = $mode === 'moving' ? '&no_moving=' . $result['no_moving'] : '&no_sj=' . $result['nomor_surat_jalan']; ?>
                        <a href="<?php echo $escape('../controller/index.php?action=create_pdf&pageState=' . $pageState . $pdfReference); ?>" target="_blank" class="gm-invoice-pdf">Create PDF</a>
                        <?php } else { ?>
                        <button type="submit" class="gm-form-submit" onclick="handleFormSubmit(event)">Create invoice &amp; PDF</button>
                        <?php } ?>
                    </div>
                </div>
            </form>
        </section>
    </div>
</main>

<?php if (!$isAmend) { ?>
<script src="../js/document_sidebar.js" defer></script>
<?php } ?>
<script src="../js/invoice.js" defer></script>

<?php include "footer.php"; ?>
