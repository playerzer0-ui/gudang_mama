<?php include "header.php"; ?>

<main class="main-container">
    <form id="myForm" action="../controller/index.php?action=create_slip" method="post">
        <h1>SLIP <?php echo $pageState; ?></h1>
        <table>
            <!-- Your form header table here -->
            <tr class="form-header">
                <td>PT</td>
                <td>:</td>
                <td colspan="2">
                    <select name="storageCode" id="storageCode" onchange="getLPB()">
                        <?php foreach (getAllStorages() as $key) { ?>
                            <?php if($key["storageCode"] == "NON") { ?>
                                <option value="<?php echo $key["storageCode"]; ?>" selected><?php echo $key["storageName"]; ?></option>
                            <?php } else { ?>
                                <option value="<?php echo $key["storageCode"]; ?>"><?php echo $key["storageName"]; ?></option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </td>
                <td>Name Vendor</td>
                <td>:</td>
                <td>
                    <select name="vendorCode" id="vendorCode">
                        <?php foreach (getAllVendors() as $key) { ?>
                            <?php if($key["vendorCode"] == "NON") { ?>
                                <option value="<?php echo $key["vendorCode"]; ?>" selected><?php echo $key["vendorName"]; ?></option>
                            <?php } else { ?>
                                <option value="<?php echo $key["vendorCode"]; ?>"><?php echo $key["vendorName"]; ?></option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>NO. LPB</td>
                <td>:</td>
                <td colspan="2">
                    <input name="no_lpb_display" type="text" id="no_lpb_display" placeholder="Otomatis dari sistem" readonly>
                    <input name="no_LPB" type="hidden" id="no_LPB">
                </td>
                <td>Tgl Penerimaan</td>
                <td>:</td>
                <td><input name="order_date" type="date" id="tgl_penerimaan" placeholder="di isi" required></td>
            </tr>
            <tr class="highlight">
                <td>No SJ</td>
                <td>:</td>
                <td colspan="2"><input name="no_sj" type="text" id="no_sj" placeholder="di isi" required></td>
                <td>No PO</td>
                <td>:</td>
                <td><input name="purchase_order" type="text" id="purchase_order" placeholder="di isi" required></td>
            </tr>
            <tr>
                <td>No Truk</td>
                <td>:</td>
                <td colspan="2"><input name="no_truk" type="text" id="no_truk" placeholder="di isi" required></td>
                <td colspan="3"></td>
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
                    <th>Note</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <!-- Rows will be added here dynamically -->
            </tbody>
        </table>
        <button type="button" class="btn btn-success" onclick="addRow()">Add Row</button>
        <button type="submit" class="btn btn-outline-success">Submit</button>
    </form>
</main>

<script>
    window.onload = function() {
        getLPB();
    };
</script>
<script src="../js/index.js" async defer></script>

<?php include "footer.php"; ?>
