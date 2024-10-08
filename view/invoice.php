<?php include "header.php"; ?>

<main class="main-container">
    <form id="myForm" action="../controller/index.php?action=create_invoice" target="_blank" method="post">
        <h1>INVOICE <?php echo $pageState; ?></h1>
        <input type="hidden" id="pageState" name="pageState" value=<?php echo $pageState; ?>>
        <table>
            <tr class="form-header">
                <?php if($pageState == "moving"){ ?>
                    <td>PT Pengirim</td>
                    <td>:</td>
                    <td><input type="text" name="storageCodeSender" id="storageCodeSender" placeholder="otomatis" readonly></td>
                    <td>PT Penerima</td>
                    <td>:</td>
                    <td><input type="text" name="storageCodeReceiver" id="storageCodeReceiver" placeholder="otomatis" readonly></td>
                <?php } else { ?>
                    <td>PT</td>
                    <td>:</td>
                    <td colspan="2"><input name="storageCode" type="text" id="storageCode" placeholder="Otomatis dari sistem" readonly></td>
                    <?php if ($pageState == "in") { ?>
                        <td>Name Vendor</td>
                        <td>:</td>
                        <td colspan="2"><input name="vendorCode" type="text" id="vendorCode" placeholder="Otomatis dari sistem" readonly></td>
                    <?php } else { ?>
                        <td>Name Customer</td>
                        <td>:</td>
                        <td colspan="2"><input name="customerCode" type="text" id="customerCode" placeholder="Otomatis dari sistem" readonly></td>
                    <?php } ?>
                <?php } ?>
            </tr>
            <tr>
                <?php if($pageState == "moving"){ ?>
                    <td>NO. moving</td>
                    <td>:</td>
                    <td><input name="no_moving" id="no_moving" type="text" oninput="getMovingDetailsFromMovingNo()" required></td>
                    <td>Tgl. moving</td>
                    <td>:</td>
                    <td><input name="moving_date" id="moving_date" type="date" readonly></td>
                <?php } else { ?>
                    <?php if ($pageState == "in") { ?>
                        <td>NO. LPB</td>
                        <td>:</td>
                        <td colspan="2"><input name="no_LPB" type="text" id="no_LPB" placeholder="Otomatis dari sistem" readonly></td>
                        <td>No PO</td>
                        <td>:</td>
                        <td colspan="2"><input name="purchase_order" type="text" id="purchase_order" placeholder="Otomatis dari sistem" readonly></td>
                    <?php } else { ?>
                        <td>No SJ</td>
                        <td>:</td>
                        <td colspan="2"><input name="no_sj" type="text" id="no_sj" placeholder="di isi" oninput="getDetailsFromSJ()" required></td>
                        <td>Alamat</td>
                        <td>:</td>
                        <td colspan="2"><input name="customerAddress" type="text" id="customerAddress" placeholder="Otomatis dari sistem" readonly></td>
                    <?php } ?>
                <?php } ?>
            </tr>
            <tr class="highlight">
            <?php if($pageState == "moving"){ ?>
                    <td>No Invoice</td>
                    <td>:</td>
                    <td><input name="no_invoice" type="text" id="no_invoice" placeholder="otomatis dari sistem" readonly></td>
                    <td>Tgl invoice</td>
                    <td>:</td>
                    <td colspan="2"><input name="invoice_date" type="date" id="invoice_date" placeholder="di isi" oninput="generateNoInvoice()" required></td>
            <?php } else { ?>
                    <?php if ($pageState == "in") { ?>
                        <td>No SJ</td>
                        <td>:</td>
                        <td colspan="2"><input name="no_sj" type="text" id="no_sj" placeholder="di isi" oninput="getDetailsFromSJ()" required></td>
                        <td>Tgl invoice</td>
                        <td>:</td>
                        <td colspan="2"><input name="invoice_date" type="date" id="invoice_date" placeholder="di isi" required></td>
                    <?php } else { ?>
                        <td>No Invoice</td>
                        <td>:</td>
                        <td colspan="2"><input name="no_invoice" type="text" id="no_invoice" placeholder="otomatis dari sistem" readonly></td>
                        <td>NPWP</td>
                        <td>:</td>
                        <td colspan="2"><input name="npwp" type="text" id="npwp" placeholder="Otomatis dari sistem" readonly></td>
                    <?php } ?>
                <?php } ?>
            </tr>
            <?php if($pageState != "moving"){ ?>
            <tr>
                <?php if ($pageState == "in") { ?>
                    <td>No Truk</td>
                    <td>:</td>
                    <td colspan="2"><input name="no_truk" type="text" id="no_truk" placeholder="Otomatis dari sistem" readonly></td>
                    <td>No Invoice</td>
                    <td>:</td>
                    <td colspan="2"><input name="no_invoice" type="text" id="no_invoice" placeholder="di isi" required></td>
                <?php } else { ?>
                    <td>Tgl invoice</td>
                    <td>:</td>
                    <td colspan="2"><input name="invoice_date" type="date" id="invoice_date" placeholder="di isi" oninput="generateNoInvoice()" required></td>
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
            </tbody>
        </table>

        <table id="accountTable">
            <tr>
                <th>No. Faktur: </th>
                <td><input type="text" name="no_faktur" id="no_faktur" placeholder="di isi" required></td>
                <th>Total Nilai Barang: </th>
                <td><input type="number" inputmode="numeric" name="totalNominal" id="totalNominal" disabled></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td>PPN(%): <input type="number" name="tax" id="tax" value="11" oninput="calculateTotalNominal()"></td>
                <td><input type="number" inputmode="numeric" name="taxPPN" id="taxPPN" disabled></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td>Nilai Dibayar: </td>
                <td><input type="number" inputmode="numeric" name="amount_paid" id="amount_paid" disabled></td>
            </tr>
        </table>
        <button type="submit" class="btn btn-outline-success" onclick="handleFormSubmit(event)">Submit</button>
    </form>
</main>

<script src="../js/invoice.js" async defer></script>

<?php include "footer.php"; ?>
