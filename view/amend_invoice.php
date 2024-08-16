<?php include "header.php"; ?>

<main class="main-container">
    <form id="myForm" action="../controller/index.php?action=amend_update_data&data=invoice" method="post">
        <h1>INVOICE <?php echo $pageState; ?></h1>
        <input type="hidden" id="pageState" name="pageState" value=<?php echo $pageState; ?>>
        <input name="old_invoice" type="hidden" id="old_invoice" placeholder="otomatis dari sistem" value="<?php echo $invoice["no_invoice"]; ?>">
        <?php if($pageState != "amend_invoice_moving"){ ?>
            <input name="old_sj" type="hidden" id="old_sj" value="<?php echo $result["nomor_surat_jalan"]; ?>">
        <?php } ?>
        <table>
            <tr class="form-header">
                <?php if($pageState == "amend_invoice_moving"){ ?>
                    <td>PT Pengirim</td>
                    <td>:</td>
                    <td><input type="text" name="storageCodeSender" id="storageCodeSender" placeholder="otomatis" value="<?php echo $result["storageCodeSender"]; ?>" readonly></td>
                    <td>PT Penerima</td>
                    <td>:</td>
                    <td><input type="text" name="storageCodeReceiver" id="storageCodeReceiver" placeholder="otomatis" value="<?php echo $result["storageCodeReceiver"]; ?>" readonly></td>
                <?php } else { ?>
                    <td>PT</td>
                    <td>:</td>
                    <td colspan="2"><input name="storageCode" type="text" id="storageCode" placeholder="Otomatis dari sistem" value="<?php echo $result["storageCode"]; ?>" readonly></td>
                    <?php if ($pageState == "amend_invoice_in") { ?>
                        <td>Name Vendor</td>
                        <td>:</td>
                        <td colspan="2"><input name="vendorCode" type="text" id="vendorCode" placeholder="Otomatis dari sistem" value="<?php echo $result["vendorCode"]; ?>" readonly></td>
                    <?php } else { ?>
                        <td>Name Customer</td>
                        <td>:</td>
                        <td colspan="2"><input name="customerCode" type="text" id="customerCode" placeholder="Otomatis dari sistem" value="<?php echo $result["customerCode"]; ?>" readonly></td>
                    <?php } ?>
                <?php } ?>
            </tr>
            <tr>
                <?php if($pageState == "amend_invoice_moving"){ ?>
                    <td>NO. moving</td>
                    <td>:</td>
                    <td><input name="no_moving" id="no_moving" type="text" value="<?php echo $result["no_moving"]; ?>" readonly></td>
                    <td>Tgl. moving</td>
                    <td>:</td>
                    <td><input name="moving_date" id="moving_date" type="date" value="<?php echo $result["moving_date"]; ?>" readonly></td>
                <?php } else { ?>
                    <?php if ($pageState == "amend_invoice_in") { ?>
                        <td>NO. LPB</td>
                        <td>:</td>
                        <td colspan="2"><input name="no_LPB" type="text" id="no_LPB" placeholder="Otomatis dari sistem" value="<?php echo $result["no_LPB"]; ?>" readonly></td>
                        <td>No PO</td>
                        <td>:</td>
                        <td colspan="2"><input name="purchase_order" type="text" id="purchase_order" placeholder="Otomatis dari sistem" value="<?php echo $result["purchase_order"]; ?>" readonly></td>
                    <?php } else { ?>
                        <td>No SJ</td>
                        <td>:</td>
                        <td colspan="2"><input name="no_sj" type="text" id="no_sj" placeholder="di isi" value="<?php echo $result["nomor_surat_jalan"]; ?>" required></td>
                        <td>Alamat</td>
                        <td>:</td>
                        <td colspan="2"><input name="customerAddress" type="text" id="customerAddress" placeholder="Otomatis dari sistem" value="<?php echo $result["customerAddress"]; ?>" readonly></td>
                    <?php } ?>
                <?php } ?>
            </tr>
            <tr class="highlight">
                <?php if($pageState == "amend_invoice_moving"){ ?>
                    <td>No Invoice</td>
                    <td>:</td>
                    <td><input name="no_invoice" type="text" id="no_invoice" placeholder="otomatis dari sistem" value="<?php echo $invoice["no_invoice"]; ?>" readonly></td>
                    <td>Tgl invoice</td>
                    <td>:</td>
                    <td colspan="2"><input name="invoice_date" type="date" id="invoice_date" placeholder="di isi" value="<?php echo $invoice["invoice_date"]; ?>" oninput="generateNoInvoice()" required></td>
                <?php } else { ?>
                    <?php if ($pageState == "amend_invoice_in") { ?>
                        <td>No SJ</td>
                        <td>:</td>
                        <td colspan="2"><input name="no_sj" type="text" id="no_sj" placeholder="di isi" value="<?php echo $result["nomor_surat_jalan"]; ?>" required></td>
                        <td>Tgl invoice</td>
                        <td>:</td>
                        <td colspan="2"><input name="invoice_date" type="date" id="invoice_date" placeholder="di isi" value="<?php echo $invoice["invoice_date"]; ?>" required></td>
                    <?php } else { ?>
                        <td>No Invoice</td>
                        <td>:</td>
                        <td colspan="2"><input name="no_invoice" type="text" id="no_invoice" placeholder="otomatis dari sistem" value="<?php echo $invoice["no_invoice"]; ?>" readonly></td>
                        <td>NPWP</td>
                        <td>:</td>
                        <td colspan="2"><input name="npwp" type="text" id="npwp" placeholder="Otomatis dari sistem" value="<?php echo $result["customerNPWP"]; ?>" readonly></td>
                    <?php } ?>
                <?php } ?>
            </tr>
            <?php if($pageState != "amend_invoice_moving"){ ?>
            <tr>
                <?php if ($pageState == "amend_invoice_in") { ?>
                    <td>No Truk</td>
                    <td>:</td>
                    <td colspan="2"><input name="no_truk" type="text" id="no_truk" placeholder="Otomatis dari sistem" value="<?php echo $result["no_truk"]; ?>" readonly></td>
                    <td>No Invoice</td>
                    <td>:</td>
                    <td colspan="2"><input name="no_invoice" type="text" id="no_invoice" placeholder="di isi" value="<?php echo $invoice["no_invoice"]; ?>" required></td>
                <?php } else { ?>
                    <td>Tgl invoice</td>
                    <td>:</td>
                    <td colspan="2"><input name="invoice_date" type="date" id="invoice_date" placeholder="di isi" oninput="generateNoInvoice()" value="<?php echo $invoice["invoice_date"]; ?>" required></td>
                    <td colspan="4"></td>
                <?php } ?>
            </tr>
            <?php } ?>
        </table>

        <table id="productTable">
            <thead>
                <tr>
                    <th>No</th>
                    <th>KD</th>
                    <th>Material</th>
                    <th>QTY</th>
                    <th>UOM</th>
                    <th>price/UOM</th>
                    <th>nominal</th>
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
                    <td><input type="text" name="kd[]" value="<?php echo $key["productCode"]; ?>" class="productCode" readonly></td>
                    <td><input style="width: 300px;" value="<?php echo $key["productName"]; ?>" type="text" name="material_display[]" readonly><input type="hidden" value="<?php echo $key["productName"]; ?>" name="material[]"></td>
                    <td><input type="number" value="<?php echo $key["qty"]; ?>" name="qty[]" readonly></td>
                    <td><input type="text" value="<?php echo $key["uom"]; ?>" name="uom[]" readonly></td>
                    <td><input type="number" value="<?php echo $key["price_per_UOM"]; ?>" inputmode="numeric" name="price_per_uom[]" placeholder="di isi" oninput="calculateNominal(this)" required></td>
                    <td><input type="text" name="nominal[]" placeholder="otomatis dari sistem" value="<?php echo (int)$key["qty"] * (double)$key["price_per_UOM"]; ?>" readonly></td>
                </tr>
                <?php 
                    $totalNominal += (int)$key["qty"] * (double)$key["price_per_UOM"];
                } ?>
            </tbody>
        </table>

        <table id="accountTable">
            <tr>
                <th>No. Faktur: </th>
                <td><input type="text" name="no_faktur" id="no_faktur" value="<?php echo $invoice["no_faktur"]; ?>" placeholder="di isi" required></td>
                <th>Total Nilai Barang: </th>
                <td><input type="number" inputmode="numeric" name="totalNominal" id="totalNominal" value="<?php echo $totalNominal; ?>" disabled></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td>PPN(%): <input type="number" name="tax" id="tax" value="<?php echo $invoice["tax"]; ?>" oninput="calculateTotalNominal()"></td>
                <td><input type="number" inputmode="numeric" name="taxPPN" id="taxPPN" value="<?php echo ($totalNominal * ($invoice["tax"] / 100)); ?>" disabled></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td>Nilai Dibayar: </td>
                <td><input type="number" inputmode="numeric" name="amount_paid" id="amount_paid" value="<?php echo (($totalNominal * ($invoice["tax"] / 100)) + $totalNominal); ?>" disabled></td>
            </tr>
        </table>
        <button type="submit" class="btn btn-outline-success">Submit</button>
        <?php if($pageState == "amend_invoice_moving"){ ?>
            <a href="<?php echo "../controller/index.php?action=create_pdf&pageState=" . $pageState . "&no_moving=" . $result["no_moving"]; ?>" target="_blank">create PDF</a>
        <?php } else { ?>
            <a href="<?php echo "../controller/index.php?action=create_pdf&pageState=" . $pageState . "&no_sj=" . $result["nomor_surat_jalan"]; ?>" target="_blank">create PDF</a>
        <?php } ?>
    </form>
</main>

<script src="../js/invoice.js" async defer></script>

<?php include "footer.php"; ?>
