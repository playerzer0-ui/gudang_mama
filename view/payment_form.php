<?php
// Create and amend payment. $pageState is in / out / out_tax / moving, or amend_payment_<mode>.
// Amend pages also receive $result, $invoice, $payment and $products from amend_controller.php.
require_once "partials/form_field.php";

$isAmend = strpos($pageState, 'amend_payment_') === 0;
$mode = $isAmend ? substr($pageState, strlen('amend_payment_')) : $pageState;
$hasDocumentSidebar = !$isAmend && in_array($mode, ['in', 'out', 'out_tax'], true);
$escape = static fn($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
// Existing values are only shown when amending.
$resultValue = static fn($key) => $isAmend ? $result[$key] : null;
$invoiceValue = static fn($key) => $isAmend ? $invoice[$key] : null;
$paymentValue = static fn($key) => $isAmend ? $payment[$key] : null;

$auto = 'Otomatis dari sistem';
// The source document can only be chosen on a new payment; amending keeps it fixed.
$noSjField = $isAmend
    ? ['no_sj', 'No SJ', ['placeholder' => 'di isi', 'value' => $resultValue('nomor_surat_jalan'), 'readonly' => true]]
    : ['no_sj', 'No SJ', ['placeholder' => 'di isi', 'oninput' => 'getDetailsFromSJ()', 'required' => true]];
$invoiceDateField = ['invoice_date', 'Tgl invoice', ['type' => 'date', 'placeholder' => $auto, 'value' => $invoiceValue('invoice_date'), 'readonly' => true]];
if ($mode === 'moving') {
    $fields = [
        ['storageCodeSender', 'PT Pengirim', ['placeholder' => 'otomatis', 'value' => $resultValue('storageCodeSender'), 'readonly' => true]],
        ['storageCodeReceiver', 'PT Penerima', ['placeholder' => 'otomatis', 'value' => $resultValue('storageCodeReceiver'), 'readonly' => true]],
        $isAmend
            ? ['no_moving', 'NO. moving', ['value' => $resultValue('no_moving'), 'readonly' => true]]
            : ['no_moving', 'NO. moving', ['oninput' => 'getMovingDetailsFromMovingNo()', 'required' => true]],
        ['moving_date', 'Tgl. moving', ['type' => 'date', 'value' => $resultValue('moving_date'), 'readonly' => true]],
        ['no_invoice', 'No Invoice', ['placeholder' => 'otomatis dari sistem', 'value' => $invoiceValue('no_invoice'), 'readonly' => true]],
        $invoiceDateField,
    ];
} elseif ($mode === 'in') {
    $fields = [
        ['storageCode', 'PT', ['placeholder' => $auto, 'value' => $resultValue('storageCode'), 'readonly' => true]],
        ['vendorCode', 'Name Vendor', ['placeholder' => $auto, 'value' => $resultValue('vendorCode'), 'readonly' => true]],
        ['no_LPB', 'NO. LPB', ['placeholder' => $auto, 'value' => $resultValue('no_LPB'), 'readonly' => true]],
        ['purchase_order', 'No PO', ['placeholder' => $auto, 'value' => $resultValue('purchase_order'), 'readonly' => true]],
        $noSjField,
        $invoiceDateField,
        ['no_truk', 'No Truk', ['placeholder' => $auto, 'value' => $resultValue('no_truk'), 'readonly' => true]],
        ['no_invoice', 'No Invoice', ['placeholder' => $auto, 'value' => $invoiceValue('no_invoice'), 'readonly' => true]],
    ];
} else {
    $fields = [
        ['storageCode', 'PT', ['placeholder' => $auto, 'value' => $resultValue('storageCode'), 'readonly' => true]],
        ['customerCode', 'Name Customer', ['placeholder' => $auto, 'value' => $resultValue('customerCode'), 'readonly' => true]],
        $noSjField,
        ['customerAddress', 'Alamat', ['placeholder' => $auto, 'value' => $resultValue('customerAddress'), 'readonly' => true]],
        ['no_invoice', 'No Invoice', ['placeholder' => $auto, 'value' => $invoiceValue('no_invoice'), 'readonly' => true]],
        ['npwp', 'NPWP', ['placeholder' => $auto, 'value' => $resultValue('customerNPWP'), 'readonly' => true]],
        $invoiceDateField,
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

<main class="gm-form-page gm-invoice-page gm-payment-page">
    <div class="gm-invoice-inner">
        <?php if ($hasDocumentSidebar) { $sidebarPurpose = 'payment'; include "source_sidebar.php"; } ?>
        <section class="document-form">
            <form id="myForm" action="../controller/index.php?action=<?php echo $isAmend ? 'amend_update_data&amp;data=payment' : 'create_payment'; ?>" method="post">
                <header class="gm-form-heading">
                    <span class="gm-form-eyebrow"><?php echo $isAmend ? 'Amend record' : 'New payment'; ?></span>
                    <div class="gm-form-title-row">
                        <h1><?php echo $isAmend ? 'Amend payment' : 'Payment'; ?></h1>
                        <span class="gm-form-mode"><?php echo $escape(str_replace('_', ' ', $mode)); ?></span>
                    </div>
                </header>
                <input type="hidden" id="pageState" name="pageState" value="<?php echo $escape($pageState); ?>">
                <?php if ($isAmend) { ?>
                <input name="payment_id" type="hidden" id="payment_id" value="<?php echo $escape($payment['payment_id']); ?>">
                <?php if ($mode !== 'moving') { ?>
                <input name="old_sj" type="hidden" id="old_sj" value="<?php echo $escape($result['nomor_surat_jalan']); ?>">
                <?php } ?>
                <?php } ?>

                <section class="gm-form-section">
                    <div class="gm-form-section-heading">
                        <div>
                            <h2>Document details</h2>
                            <p>Invoice information from the selected document.</p>
                        </div>
                    </div>
                    <div class="gm-form-fields">
                        <?php foreach ($fields as [$id, $label, $options]) form_field($id, $label, $options); ?>
                    </div>
                </section>

                <section class="gm-form-section">
                    <div class="gm-form-section-heading">
                        <div>
                            <h2>Materials <span id="paymentRowCount" class="gm-form-row-count"></span></h2>
                            <p>Quantities and prices from the invoice.</p>
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
                                    <th scope="col">Price / UOM</th>
                                    <th scope="col">Nominal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- New payments get their rows from payment.js -->
                                <?php if ($isAmend) foreach ($products as $index => $product) { ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><input type="text" name="kd[]" value="<?php echo $escape($product['productCode']); ?>" class="productCode" readonly></td>
                                    <td><input value="<?php echo $escape($product['productName']); ?>" type="text" name="material_display[]" readonly><input type="hidden" value="<?php echo $escape($product['productName']); ?>" name="material[]"></td>
                                    <td><input type="number" value="<?php echo $escape($product['qty']); ?>" name="qty[]" readonly></td>
                                    <td><input type="text" value="<?php echo $escape($product['uom']); ?>" name="uom[]" readonly></td>
                                    <td><input type="number" value="<?php echo $escape($product['price_per_UOM']); ?>" inputmode="numeric" name="price_per_uom[]" placeholder="di isi" oninput="calculateNominal(this)" readonly></td>
                                    <td><input type="text" name="nominal[]" placeholder="otomatis dari sistem" value="<?php echo (int) $product['qty'] * (float) $product['price_per_UOM']; ?>" readonly></td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <p id="paymentEmptyState" class="gm-form-empty" hidden>Select a document to load its invoice materials.</p>
                    <p class="gm-form-table-hint">Scroll horizontally to see prices and amounts.</p>
                </section>

                <section id="accountTable" class="gm-form-section">
                    <div class="gm-form-section-heading">
                        <div>
                            <h2>Payment details</h2>
                            <p>Fields marked * are required.</p>
                        </div>
                    </div>
                    <div class="gm-invoice-summary">
                        <div class="gm-payment-entry">
                            <?php form_field('payment_date', 'Tanggal payment', ['type' => 'date', 'placeholder' => 'di isi', 'value' => $paymentValue('payment_date'), 'required' => true]); ?>
                            <div class="gm-form-field"><label for="payment_amount">Nilai payment *</label><input type="number" inputmode="numeric" name="payment_amount" id="payment_amount" oninput="calculateHutang()"<?php if ($isAmend) echo ' value="' . $escape($payment['payment_amount']) . '"'; ?> required></div>
                            <div class="gm-payment-remaining"><span>Sisa hutang</span><strong id="remaining" aria-live="polite">0</strong></div>
                        </div>
                        <div class="gm-invoice-totals">
                            <div class="gm-invoice-total-row"><label for="totalNominal">Total Nilai Barang</label><input type="number" inputmode="numeric" name="totalNominal" id="totalNominal"<?php if ($isAmend) echo ' value="' . $totalNominal . '"'; ?> disabled></div>
                            <div class="gm-invoice-total-row"><label for="tax">PPN (%)</label><input type="number" name="tax" id="tax"<?php if ($isAmend) echo ' value="' . $escape($invoice['tax']) . '"'; ?> oninput="calculateTotalNominal()" readonly></div>
                            <div class="gm-invoice-total-row"><label for="taxPPN">Nilai PPN</label><input type="number" inputmode="numeric" name="taxPPN" id="taxPPN"<?php if ($isAmend) echo ' value="' . $taxPPN . '"'; ?> disabled></div>
                            <div class="gm-invoice-total-row"><label for="amount_paid">Total invoice</label><input type="number" inputmode="numeric" name="amount_paid" id="amount_paid"<?php if ($isAmend) echo ' value="' . ($taxPPN + $totalNominal) . '"'; ?> disabled></div>
                        </div>
                    </div>
                </section>

                <div class="gm-form-actions">
                    <p>Review the payment date and amount before saving.</p>
                    <div class="gm-invoice-buttons">
                        <button type="submit" class="gm-form-submit" onclick="handleFormSubmit(event)"><?php echo $isAmend ? 'Save changes' : 'Save payment &amp; PDF'; ?></button>
                        <?php if ($isAmend) { ?>
                        <?php $pdfReference = $mode === 'moving' ? '&no_moving=' . $result['no_moving'] : '&no_sj=' . $result['nomor_surat_jalan']; ?>
                        <a href="<?php echo $escape('../controller/index.php?action=create_pdf&pageState=' . $pageState . $pdfReference . '&payment_id=' . $payment['payment_id']); ?>" target="_blank" class="gm-invoice-pdf">Create PDF</a>
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
<script src="../js/payment.js" defer></script>

<?php include "footer.php"; ?>
