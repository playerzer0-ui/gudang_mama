<?php include "header.php"; ?>

<main class="main-container">
    <form id="myForm" action="../controller/index.php?action=create_payment" method="post">
    <h1>PAYMENT <?php echo $pageState; ?></h1>
    <input type="hidden" id="pageState" name="pageState" value=<?php echo $pageState; ?>>
    <table>
        <tr class="form-header">
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
        </tr>
        <tr>
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
        </tr>
        <tr class="highlight">
            <?php if ($pageState == "in") { ?>
                <td>No SJ</td>
                <td>:</td>
                <td colspan="2"><input name="no_sj" type="text" id="no_sj" placeholder="di isi" oninput="getDetailsFromSJ()" required></td>
                <td>Tgl invoice</td>
                <td>:</td>
                <td colspan="2"><input name="invoice_date" type="date" id="invoice_date" placeholder="Otomatis dari sistem" readonly></td>
            <?php } else { ?>
                <td>No Invoice</td>
                <td>:</td>
                <td colspan="2"><input name="no_invoice" type="text" id="no_invoice" placeholder="Otomatis dari sistem" readonly></td>
                <td>NPWP</td>
                <td>:</td>
                <td colspan="2"><input name="npwp" type="text" id="npwp" placeholder="Otomatis dari sistem" readonly></td>
            <?php } ?>
        </tr>
        <tr>
            <?php if ($pageState == "in") { ?>
                <td>No Truk</td>
                <td>:</td>
                <td colspan="2"><input name="no_truk" type="text" id="no_truk" placeholder="Otomatis dari sistem" readonly></td>
                <td>No Invoice</td>
                <td>:</td>
                <td colspan="2"><input name="no_invoice" type="text" id="no_invoice" placeholder="Otomatis dari sistem" readonly></td>
            <?php } else { ?>
                <td>Tgl invoice</td>
                <td>:</td>
                <td colspan="2"><input name="invoice_date" type="date" id="invoice_date" placeholder="Otomatis dari sistem" readonly></td>
                <td colspan="4"></td>
            <?php } ?>
        </tr>
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
            <th>tanggal payment: </th>
            <td><input type="date" name="payment_date" id="payment_date" placeholder="di isi" required></td>
            <th>total nilai barang: </th>
            <th><input type="number" inputmode="numeric" name="totalNominal" id="totalNominal" disabled></th>
        </tr>
        <tr>
            <td>nilai payment: </td>
            <td><input type="number" inputmode="numeric" name="payment_amount" id="payment_amount" required></td>
            <td>PPN (11%): </td>
            <td><input type="number" inputmode="numeric" name="taxPPN" id="taxPPN" disabled></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td>nilai dibayar: </td>
            <td><input type="number" inputmode="numeric" name="amount_paid" id="amount_paid" disabled></td>
        </tr>
    </table>
    <button type="submit" class="btn btn-outline-success" onclick="handleFormSubmit(event)">Submit</button>
    </form>
</main>

<script src="../js/payment.js" async defer></script>

<?php include "footer.php"; ?>