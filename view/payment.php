<?php include "header.php"; ?>

<?php $hasDocumentSidebar = in_array($pageState, ['in', 'out', 'out_tax'], true); ?>
<main class="gm-slip-page gm-invoice-page gm-payment-page"><div class="gm-invoice-inner">
    <?php if ($hasDocumentSidebar) include __DIR__ . '/payment_sidebar.php'; ?>
    <section class="document-form">
    <form id="myForm" action="../controller/index.php?action=create_payment" method="post">
    <header class="gm-slip-heading"><span class="gm-slip-eyebrow">New payment</span><div class="gm-slip-title-row"><h1>Payment</h1><span class="gm-slip-mode"><?php echo htmlspecialchars(str_replace(['amend_payment_', '_'], ['', ' '], $pageState), ENT_QUOTES, 'UTF-8'); ?></span></div><p>Select a document, review its invoice, then enter the payment.</p></header>
    <input type="hidden" id="pageState" name="pageState" value="<?php echo htmlspecialchars($pageState, ENT_QUOTES, 'UTF-8'); ?>">
<section class="gm-slip-section">
<div class="gm-slip-section-heading"><div><h2>Document details</h2><p>Invoice information from the selected document.</p></div></div>
<div class="gm-slip-fields">

            <?php if($pageState == "moving"){ ?>
                <div class="gm-slip-field"><label for="storageCodeSender">PT Pengirim</label><input type="text" name="storageCodeSender" id="storageCodeSender" placeholder="otomatis" readonly></div>
                <div class="gm-slip-field"><label for="storageCodeReceiver">PT Penerima</label><input type="text" name="storageCodeReceiver" id="storageCodeReceiver" placeholder="otomatis" readonly></div>
            <?php } else { ?>
                <div class="gm-slip-field"><label for="storageCode">PT</label><input name="storageCode" type="text" id="storageCode" placeholder="Otomatis dari sistem" readonly></div>
                <?php if ($pageState == "in") { ?>
                    <div class="gm-slip-field"><label for="vendorCode">Name Vendor</label><input name="vendorCode" type="text" id="vendorCode" placeholder="Otomatis dari sistem" readonly></div>
                <?php } else { ?>
                    <div class="gm-slip-field"><label for="customerCode">Name Customer</label><input name="customerCode" type="text" id="customerCode" placeholder="Otomatis dari sistem" readonly></div>
                <?php } ?>
            <?php } ?>

            <?php if($pageState == "moving"){ ?>
                <div class="gm-slip-field"><label for="no_moving">NO. moving *</label><input name="no_moving" id="no_moving" type="text" oninput="getMovingDetailsFromMovingNo()" required></div>
                <div class="gm-slip-field"><label for="moving_date">Tgl. moving</label><input name="moving_date" id="moving_date" type="date" readonly></div>
            <?php } else { ?>
                <?php if ($pageState == "in") { ?>
                    <div class="gm-slip-field"><label for="no_LPB">NO. LPB</label><input name="no_LPB" type="text" id="no_LPB" placeholder="Otomatis dari sistem" readonly></div>
                    <div class="gm-slip-field"><label for="purchase_order">No PO</label><input name="purchase_order" type="text" id="purchase_order" placeholder="Otomatis dari sistem" readonly></div>
                <?php } else { ?>
                    <div class="gm-slip-field"><label for="no_sj">No SJ *</label><input name="no_sj" type="text" id="no_sj" placeholder="di isi" oninput="getDetailsFromSJ()" required></div>
                    <div class="gm-slip-field"><label for="customerAddress">Alamat</label><input name="customerAddress" type="text" id="customerAddress" placeholder="Otomatis dari sistem" readonly></div>
                <?php } ?>
            <?php } ?>

            <?php if($pageState == "moving"){ ?>
                <div class="gm-slip-field"><label for="no_invoice">No Invoice</label><input name="no_invoice" type="text" id="no_invoice" placeholder="otomatis dari sistem" readonly></div>
                <div class="gm-slip-field"><label for="invoice_date">Tgl invoice *</label><input name="invoice_date" type="date" id="invoice_date" placeholder="Otomatis dari sistem" readonly></div>
            <?php } else { ?>
                <?php if ($pageState == "in") { ?>
                    <div class="gm-slip-field"><label for="no_sj">No SJ *</label><input name="no_sj" type="text" id="no_sj" placeholder="di isi" oninput="getDetailsFromSJ()" required></div>
                    <div class="gm-slip-field"><label for="invoice_date">Tgl invoice</label><input name="invoice_date" type="date" id="invoice_date" placeholder="Otomatis dari sistem" readonly></div>
                <?php } else { ?>
                    <div class="gm-slip-field"><label for="no_invoice">No Invoice</label><input name="no_invoice" type="text" id="no_invoice" placeholder="Otomatis dari sistem" readonly></div>
                    <div class="gm-slip-field"><label for="npwp">NPWP</label><input name="npwp" type="text" id="npwp" placeholder="Otomatis dari sistem" readonly></div>
                <?php } ?>
            <?php } ?>

        <?php if($pageState != "moving"){ ?>

            <?php if ($pageState == "in") { ?>
                <div class="gm-slip-field"><label for="no_truk">No Truk</label><input name="no_truk" type="text" id="no_truk" placeholder="Otomatis dari sistem" readonly></div>
                <div class="gm-slip-field"><label for="no_invoice">No Invoice</label><input name="no_invoice" type="text" id="no_invoice" placeholder="Otomatis dari sistem" readonly></div>
            <?php } else { ?>
                <div class="gm-slip-field"><label for="invoice_date">Tgl invoice</label><input name="invoice_date" type="date" id="invoice_date" placeholder="Otomatis dari sistem" readonly></div>

            <?php } ?>

        <?php } ?>
    </div></section>

    <section class="gm-slip-section">
<div class="gm-slip-section-heading"><div><h2>Materials <span id="paymentRowCount" class="gm-slip-row-count"></span></h2><p>Quantities and prices from the invoice.</p></div></div>
<div class="gm-slip-table-scroll"><table id="productTable">
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
            <!-- Rows will be added here dynamically -->
        </tbody>
    </table></div>
<p id="paymentEmptyState" class="gm-slip-empty" hidden>Select a document to load its invoice materials.</p>
<p class="gm-slip-table-hint">Scroll horizontally to see prices and amounts.</p>
</section>

    <section id="accountTable" class="gm-slip-section">
    <div class="gm-slip-section-heading"><div><h2>Payment details</h2><p>Fields marked * are required.</p></div></div>
    <div class="gm-invoice-summary">
        <div class="gm-payment-entry">
            <div class="gm-slip-field"><label for="payment_date">Tanggal payment *</label><input type="date" name="payment_date" id="payment_date" placeholder="di isi" required></div>
            <div class="gm-slip-field"><label for="payment_amount">Nilai payment *</label><input type="number" inputmode="numeric" name="payment_amount" id="payment_amount" oninput="calculateHutang()" required></div>
            <div class="gm-payment-remaining"><span>Sisa hutang</span><strong id="remaining" aria-live="polite">0</strong></div>
        </div>
        <div class="gm-invoice-totals">
            <div class="gm-invoice-total-row"><label for="totalNominal">Total Nilai Barang</label><input type="number" inputmode="numeric" name="totalNominal" id="totalNominal" disabled></div>
            <div class="gm-invoice-total-row"><label for="tax">PPN (%)</label><input type="number" name="tax" id="tax" oninput="calculateTotalNominal()" readonly></div>
            <div class="gm-invoice-total-row"><label for="taxPPN">Nilai PPN</label><input type="number" inputmode="numeric" name="taxPPN" id="taxPPN" disabled></div>
            <div class="gm-invoice-total-row"><label for="amount_paid">Total invoice</label><input type="number" inputmode="numeric" name="amount_paid" id="amount_paid" disabled></div>
        </div>
    </div>
</section>
<div class="gm-slip-actions"><p>Review the payment date and amount before saving.</p><div class="gm-invoice-buttons">
    <button type="submit" class="gm-slip-submit" onclick="handleFormSubmit(event)">Save payment &amp; PDF</button>
    </div></div>
</form>
    </section>
</div></main>

<script src="../js/document_sidebar.js" defer></script>

<script src="../js/payment.js" defer></script>

<?php include "footer.php"; ?>
