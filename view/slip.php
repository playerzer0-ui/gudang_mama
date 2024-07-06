<?php include "header.php"; ?>

<main class="main-container">
    <form id="myForm" action="../controller/index.php?action=create_slip" method="post">
        <h1>SLIP <?php echo $pageState; ?></h1>
        <input type="hidden" id="pageState" name="pageState" value=<?php echo $pageState; ?>>
        <table>
            <!-- Your form header table here -->
            <tr class="form-header">
                <td>PT</td>
                <td>:</td>
                <td colspan="2">
                    <?php if($pageState == "out" || $pageState == "out_tax"){ ?>
                        <select name="storageCode" id="storageCode" readonly>
                            <option value="NON" selected>none</option>
                        </select>
                    <?php } else if($pageState == "in") { ?>
                        <select name="storageCode" id="storageCode" onchange="getLPB()" readonly>
                        <?php foreach (getAllStorages() as $key) { ?>
                            <?php if($key["storageCode"] == "NON") { ?>
                                <option value="<?php echo $key["storageCode"]; ?>" selected><?php echo $key["storageName"]; ?></option>
                            <?php } else { ?>
                                <option value="<?php echo $key["storageCode"]; ?>"><?php echo $key["storageName"]; ?></option>
                            <?php } ?>
                        <?php } ?>
                        </select>
                    <?php } ?>
                </td>
                <?php if($pageState == "in"){ ?>
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
                <?php } else { ?>
                <td>Name customer</td>
                <td>:</td>
                <td>
                    <select name="customerCode" id="customerCode">
                        <?php foreach (getAllCustomers() as $key) { ?>
                            <?php if($key["vendorCode"] == "NON") { ?>
                                <option value="<?php echo $key["customerCode"]; ?>" selected><?php echo $key["customerName"]; ?></option>
                            <?php } else { ?>
                                <option value="<?php echo $key["customerCode"]; ?>"><?php echo $key["customerName"]; ?></option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </td> 
                <?php } ?>
            </tr>
            <tr>
            <?php if($pageState == "in"){ ?>
                <td>NO. LPB</td>
                <td>:</td>
                <td colspan="2">
                    <input name="no_lpb_display" type="text" id="no_lpb_display" placeholder="Otomatis dari sistem" readonly>
                    <input name="no_LPB" type="hidden" id="no_LPB">
                </td>
            <?php } else { ?>
                <td>No SJ</td>
                <td>:</td>
                <td colspan="2"><input name="no_sj" type="text" id="no_sj" placeholder="di isi" readonly></td>
            <?php } ?>
                <td>Tgl Penerimaan</td>
                <td>:</td>
                <td><input name="order_date" type="date" id="tgl_penerimaan" placeholder="di isi" required></td>
            </tr>
            <tr class="highlight">
            <?php if($pageState == "in"){ ?>
                <td>No SJ</td>
                <td>:</td>
                <td colspan="2"><input name="no_sj" type="text" id="no_sj" placeholder="di isi" required></td>
            <?php } else { ?>
                <td>No Truk</td>
                <td>:</td>
                <td colspan="2"><input name="no_truk" type="text" id="no_truk" placeholder="di isi" required></td>
            <?php } ?>
                <td>No PO</td>
                <td>:</td>
                <td><input name="purchase_order" type="text" id="purchase_order" placeholder="di isi" required></td>
            </tr>
            <tr>
            <?php if($pageState == "in"){ ?>
                <td>No Truk</td>
                <td>:</td>
                <td colspan="2"><input name="no_truk" type="text" id="no_truk" placeholder="di isi" required></td>
                <td colspan="3"></td>
            <?php } else { ?>
                <td></td>
                <td></td>
                <td colspan="2"></td>
                <td colspan="3"></td>
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
    <?php if($pageState == "in"){ ?>
    window.onload = function() {
        getLPB();
    };
    <?php } else if($pageState == "out") { ?>
    window.onload = function() {
        getSJ();
    };
    <?php } else { ?>
    window.onload = function() {
        getSJT();
    };
    <?php } ?>
</script>
<script src="../js/index.js" async defer></script>

<?php include "footer.php"; ?>
