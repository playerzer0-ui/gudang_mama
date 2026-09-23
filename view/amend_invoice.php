<?php include "header.php"; ?>

<main class="gm-slip-page gm-invoice-page"><div class="gm-invoice-inner">
    <section class="document-form"><form id="myForm" action="../controller/index.php?action=amend_update_data&data=invoice" method="post">
        <header class="gm-slip-heading"><span class="gm-slip-eyebrow">Amend record</span><div class="gm-slip-title-row"><h1>Amend invoice</h1><span class="gm-slip-mode"><?php echo htmlspecialchars(str_replace(['amend_invoice_', '_'], ['', ' '], $pageState), ENT_QUOTES, 'UTF-8'); ?></span></div><p>Update prices and invoice details for this document.</p><p class="gm-slip-reference">Editing <strong><?php echo htmlspecialchars($invoice["no_invoice"], ENT_QUOTES, "UTF-8"); ?></strong></p></header>
        <input type="hidden" id="pageState" name="pageState" value="<?php echo htmlspecialchars($pageState, ENT_QUOTES, 'UTF-8'); ?>">
        <input name="old_invoice" type="hidden" id="old_invoice" placeholder="otomatis dari sistem" value="<?php echo htmlspecialchars((string) $invoice["no_invoice"], ENT_QUOTES, 'UTF-8'); ?>">
        <?php if($pageState != "amend_invoice_moving"){ ?>
            <input name="old_sj" type="hidden" id="old_sj" value="<?php echo htmlspecialchars((string) $result["nomor_surat_jalan"], ENT_QUOTES, 'UTF-8'); ?>">
        <?php } ?>
<section class="gm-slip-section">
            <div class="gm-slip-section-heading"><div><h2>Invoice details</h2><p>Fields marked * are required.</p></div></div>
            <div class="gm-slip-fields">

                <?php if($pageState == "amend_invoice_moving"){ ?>
                    <div class="gm-slip-field"><label for="storageCodeSender">PT Pengirim</label><input type="text" name="storageCodeSender" id="storageCodeSender" placeholder="otomatis" value="<?php echo htmlspecialchars((string) $result["storageCodeSender"], ENT_QUOTES, 'UTF-8'); ?>" readonly></div>
                    <div class="gm-slip-field"><label for="storageCodeReceiver">PT Penerima</label><input type="text" name="storageCodeReceiver" id="storageCodeReceiver" placeholder="otomatis" value="<?php echo htmlspecialchars((string) $result["storageCodeReceiver"], ENT_QUOTES, 'UTF-8'); ?>" readonly></div>
                <?php } else { ?>
                    <div class="gm-slip-field"><label for="storageCode">PT</label><input name="storageCode" type="text" id="storageCode" placeholder="Otomatis dari sistem" value="<?php echo htmlspecialchars((string) $result["storageCode"], ENT_QUOTES, 'UTF-8'); ?>" readonly></div>
                    <?php if ($pageState == "amend_invoice_in") { ?>
                        <div class="gm-slip-field"><label for="vendorCode">Name Vendor</label><input name="vendorCode" type="text" id="vendorCode" placeholder="Otomatis dari sistem" value="<?php echo htmlspecialchars((string) $result["vendorCode"], ENT_QUOTES, 'UTF-8'); ?>" readonly></div>
                    <?php } else { ?>
                        <div class="gm-slip-field"><label for="customerCode">Name Customer</label><input name="customerCode" type="text" id="customerCode" placeholder="Otomatis dari sistem" value="<?php echo htmlspecialchars((string) $result["customerCode"], ENT_QUOTES, 'UTF-8'); ?>" readonly></div>
                    <?php } ?>
                <?php } ?>

                <?php if($pageState == "amend_invoice_moving"){ ?>
                    <div class="gm-slip-field"><label for="no_moving">NO. moving</label><input name="no_moving" id="no_moving" type="text" value="<?php echo htmlspecialchars((string) $result["no_moving"], ENT_QUOTES, 'UTF-8'); ?>" readonly></div>
                    <div class="gm-slip-field"><label for="moving_date">Tgl. moving</label><input name="moving_date" id="moving_date" type="date" value="<?php echo htmlspecialchars((string) $result["moving_date"], ENT_QUOTES, 'UTF-8'); ?>" readonly></div>
                <?php } else { ?>
                    <?php if ($pageState == "amend_invoice_in") { ?>
                        <div class="gm-slip-field"><label for="no_LPB">NO. LPB</label><input name="no_LPB" type="text" id="no_LPB" placeholder="Otomatis dari sistem" value="<?php echo htmlspecialchars((string) $result["no_LPB"], ENT_QUOTES, 'UTF-8'); ?>" readonly></div>
                        <div class="gm-slip-field"><label for="purchase_order">No PO</label><input name="purchase_order" type="text" id="purchase_order" placeholder="Otomatis dari sistem" value="<?php echo htmlspecialchars((string) $result["purchase_order"], ENT_QUOTES, 'UTF-8'); ?>" readonly></div>
                    <?php } else { ?>
                        <div class="gm-slip-field"><label for="no_sj">No SJ *</label><input name="no_sj" type="text" id="no_sj" placeholder="di isi" value="<?php echo htmlspecialchars((string) $result["nomor_surat_jalan"], ENT_QUOTES, 'UTF-8'); ?>" required></div>
                        <div class="gm-slip-field"><label for="customerAddress">Alamat</label><input name="customerAddress" type="text" id="customerAddress" placeholder="Otomatis dari sistem" value="<?php echo htmlspecialchars((string) $result["customerAddress"], ENT_QUOTES, 'UTF-8'); ?>" readonly></div>
                    <?php } ?>
                <?php } ?>

                <?php if($pageState == "amend_invoice_moving"){ ?>
                    <div class="gm-slip-field"><label for="no_invoice">No Invoice</label><input name="no_invoice" type="text" id="no_invoice" placeholder="otomatis dari sistem" value="<?php echo htmlspecialchars((string) $invoice["no_invoice"], ENT_QUOTES, 'UTF-8'); ?>" readonly></div>
                    <div class="gm-slip-field"><label for="invoice_date">Tgl invoice *</label><input name="invoice_date" type="date" id="invoice_date" placeholder="di isi" value="<?php echo htmlspecialchars((string) $invoice["invoice_date"], ENT_QUOTES, 'UTF-8'); ?>" oninput="generateNoInvoice()" required></div>
                <?php } else { ?>
                    <?php if ($pageState == "amend_invoice_in") { ?>
                        <div class="gm-slip-field"><label for="no_sj">No SJ *</label><input name="no_sj" type="text" id="no_sj" placeholder="di isi" value="<?php echo htmlspecialchars((string) $result["nomor_surat_jalan"], ENT_QUOTES, 'UTF-8'); ?>" required></div>
                        <div class="gm-slip-field"><label for="invoice_date">Tgl invoice *</label><input name="invoice_date" type="date" id="invoice_date" placeholder="di isi" value="<?php echo htmlspecialchars((string) $invoice["invoice_date"], ENT_QUOTES, 'UTF-8'); ?>" required></div>
                    <?php } else { ?>
                        <div class="gm-slip-field"><label for="no_invoice">No Invoice</label><input name="no_invoice" type="text" id="no_invoice" placeholder="otomatis dari sistem" value="<?php echo htmlspecialchars((string) $invoice["no_invoice"], ENT_QUOTES, 'UTF-8'); ?>" readonly></div>
                        <div class="gm-slip-field"><label for="npwp">NPWP</label><input name="npwp" type="text" id="npwp" placeholder="Otomatis dari sistem" value="<?php echo htmlspecialchars((string) $result["customerNPWP"], ENT_QUOTES, 'UTF-8'); ?>" readonly></div>
                    <?php } ?>
                <?php } ?>

            <?php if($pageState != "amend_invoice_moving"){ ?>

                <?php if ($pageState == "amend_invoice_in") { ?>
                    <div class="gm-slip-field"><label for="no_truk">No Truk</label><input name="no_truk" type="text" id="no_truk" placeholder="Otomatis dari sistem" value="<?php echo htmlspecialchars((string) $result["no_truk"], ENT_QUOTES, 'UTF-8'); ?>" readonly></div>
                    <div class="gm-slip-field"><label for="no_invoice">No Invoice *</label><input name="no_invoice" type="text" id="no_invoice" placeholder="di isi" value="<?php echo htmlspecialchars((string) $invoice["no_invoice"], ENT_QUOTES, 'UTF-8'); ?>" required></div>
                <?php } else { ?>
                    <div class="gm-slip-field"><label for="invoice_date">Tgl invoice *</label><input name="invoice_date" type="date" id="invoice_date" placeholder="di isi" oninput="generateNoInvoice()" value="<?php echo htmlspecialchars((string) $invoice["invoice_date"], ENT_QUOTES, 'UTF-8'); ?>" required></div>

                <?php } ?>

            <?php } ?>
        </div></section>

        <section class="gm-slip-section">
            <div class="gm-slip-section-heading"><div><h2>Materials <span id="invoiceRowCount" class="gm-slip-row-count"></span></h2><p>Quantities come from the source document. Enter the price per unit.</p></div></div>
            <div class="gm-slip-table-scroll"><table id="productTable">
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
                <!-- Rows will be added here dynamically -->
                <?php
                $count = 1;
                $totalNominal = 0;
                foreach($products as $key){ ?>
                <tr>
                    <td><?php echo $count++; ?></td>
                    <td><input type="text" name="kd[]" value="<?php echo htmlspecialchars((string) $key["productCode"], ENT_QUOTES, 'UTF-8'); ?>" class="productCode" readonly></td>
                    <td><input value="<?php echo htmlspecialchars((string) $key["productName"], ENT_QUOTES, 'UTF-8'); ?>" type="text" name="material_display[]" readonly><input type="hidden" value="<?php echo htmlspecialchars((string) $key["productName"], ENT_QUOTES, 'UTF-8'); ?>" name="material[]"></td>
                    <td><input type="number" value="<?php echo htmlspecialchars((string) $key["qty"], ENT_QUOTES, 'UTF-8'); ?>" name="qty[]" readonly></td>
                    <td><input type="text" value="<?php echo htmlspecialchars((string) $key["uom"], ENT_QUOTES, 'UTF-8'); ?>" name="uom[]" readonly></td>
                    <td><input type="number" value="<?php echo htmlspecialchars((string) $key["price_per_UOM"], ENT_QUOTES, 'UTF-8'); ?>" inputmode="numeric" name="price_per_uom[]" placeholder="di isi" oninput="calculateNominal(this)" required></td>
                    <td><input type="text" name="nominal[]" placeholder="otomatis dari sistem" value="<?php echo (int)$key["qty"] * (double)$key["price_per_UOM"]; ?>" readonly></td>
                </tr>
                <?php
                    $totalNominal += (int)$key["qty"] * (double)$key["price_per_UOM"];
                } ?>
            </tbody>
        </table></div>
            <p id="invoiceEmptyState" class="gm-slip-empty" hidden>Select a source document to load its materials.</p>
            <p class="gm-slip-table-hint">Scroll horizontally to see prices and amounts.</p>
        </section>

        <section id="accountTable" class="gm-slip-section gm-invoice-account"><div class="gm-slip-section-heading"><h2>Invoice total</h2></div><div class="gm-invoice-summary"><div class="gm-slip-field"><label for="no_faktur">No. Faktur *</label><input type="text" name="no_faktur" id="no_faktur" value="<?php echo htmlspecialchars((string) $invoice["no_faktur"], ENT_QUOTES, 'UTF-8'); ?>" placeholder="di isi" required></div><div class="gm-invoice-totals">
                    <div class="gm-invoice-total-row"><label for="totalNominal">Total Nilai Barang</label><input type="number" inputmode="numeric" name="totalNominal" id="totalNominal" value="<?php echo $totalNominal; ?>" disabled></div>
                    <div class="gm-invoice-total-row"><label for="tax">PPN (%)</label><input type="number" name="tax" id="tax" value="<?php echo htmlspecialchars((string) $invoice["tax"], ENT_QUOTES, 'UTF-8'); ?>" oninput="calculateTotalNominal()"></div>
                    <div class="gm-invoice-total-row"><label for="taxPPN">Nilai PPN</label><input type="number" inputmode="numeric" name="taxPPN" id="taxPPN" value="<?php echo ($totalNominal * ($invoice["tax"] / 100)); ?>" disabled></div>
                    <div class="gm-invoice-total-row"><label for="amount_paid">Nilai Dibayar</label><input type="number" inputmode="numeric" name="amount_paid" id="amount_paid" value="<?php echo (($totalNominal * ($invoice["tax"] / 100)) + $totalNominal); ?>" disabled></div>
                </div>
            </div>
        </section>
        <div class="gm-slip-actions"><p>Review prices and tax before saving.</p><div class="gm-invoice-buttons">
        <button type="submit" class="gm-slip-submit">Save changes</button>
        <?php if($pageState == "amend_invoice_moving"){ ?>
            <a href="<?php echo "../controller/index.php?action=create_pdf&pageState=" . $pageState . "&no_moving=" . $result["no_moving"]; ?>" target="_blank" class="gm-invoice-pdf">Create PDF</a>
        <?php } else { ?>
            <a href="<?php echo "../controller/index.php?action=create_pdf&pageState=" . $pageState . "&no_sj=" . $result["nomor_surat_jalan"]; ?>" target="_blank" class="gm-invoice-pdf">Create PDF</a>
        <?php } ?>
    </div>
        </div>
    </form>
</section></div></main>

<script src="../js/invoice.js" defer></script>

<?php include "footer.php"; ?>
