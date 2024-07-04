<?php include "header.php"; ?>

<main class="main-container">
    <form id="myForm" action="../controller/index.php?action=create_invoice" target="_blank" method="post">
    <h1>INVOICE <?php echo $pageState; ?></h1>
    <table>
        <tr class="form-header">
            <td>PT</td>
            <td>:</td>
            <td colspan="2"><input name="storageCode" type="text" id="storageCode" placeholder="Otomatis dari sistem" readonly></td>
            <td>Name Vendor</td>
            <td>:</td>
            <td><input name="vendorCode" type="text" id="vendorCode" placeholder="Otomatis dari sistem" readonly></td>
        </tr>
        <tr>
            <td>NO. LPB</td>
            <td>:</td>
            <td colspan="2"><input name="no_LPB" type="text" id="no_LPB" placeholder="Otomatis dari sistem" readonly></td>
            <td>No PO</td>
            <td>:</td>
            <td><input name="purchase_order" type="text" id="purchase_order" placeholder="Otomatis dari sistem" readonly></td>
        </tr>
        <tr class="highlight">
            <td>No SJ</td>
            <td>:</td>
            <td colspan="2"><input name="no_sj" type="text" id="no_sj" placeholder="di isi" oninput="getDetailsFromSJ()" required></td>
            <td>Tgl invoice</td>
            <td>:</td>
            <td><input name="invoice_date" type="date" id="invoice_date" placeholder="di isi" required></td>
        </tr>
        <tr>
            <td>No Truk</td>
            <td>:</td>
            <td colspan="2"><input name="no_truk" type="text" id="no_truk" placeholder="Otomatis dari sistem" readonly></td>
            <td>no invoice</td>
            <td>:</td>
            <td><input name="no_invoice" type="text" id="no_invoice" placeholder="di isi" required></td>
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
            <th>no. Faktur: </th>
            <td><input type="text" name="no_faktur" id="no_faktur" placeholder="di isi" required></td>
            <th>total nilai barang: </th>
            <th><input type="number" inputmode="numeric" name="totalNominal" id="totalNominal" disabled></th>
        </tr>
        <tr>
            <td></td>
            <td></td>
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

<script src="../js/invoice.js" async defer></script>

<?php include "footer.php"; ?>