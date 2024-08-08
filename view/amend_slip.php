<?php include "header.php"; ?>

<main class="main-container">
    <form id="myForm" action="../controller/index.php?action=amend_update_data&data=slip" method="post">
        <h1>SLIP <?php echo $pageState; ?></h1>
        <input type="hidden" id="pageState" name="pageState" value=<?php echo $pageState; ?>>
        <input name="old_sj" type="hidden" id="old_sj" value="<?php echo $result["nomor_surat_jalan"]; ?>">
        <table>
            <!-- Your form header table here -->
            <tr class="form-header">
                <td>PT</td>
                <td>:</td>
                <td colspan="2">
                    <?php if($pageState == "amend_slip_out"){ ?>
                        <select name="storageCode" id="storageCode" readonly>
                            <option value="NON" selected>none</option>
                        </select>
                    <?php } else if($pageState == "amend_slip_in") { ?>
                        <select name="storageCode" id="storageCode" onchange="getLPB()" readonly>
                        <?php foreach (getAllStorages() as $key) { ?>
                            <?php if($key["storageCode"] == $result["storageCode"]) { ?>
                                <option value="<?php echo $key["storageCode"]; ?>" selected><?php echo $key["storageName"]; ?></option>
                            <?php } else { ?>
                                <option value="<?php echo $key["storageCode"]; ?>"><?php echo $key["storageName"]; ?></option>
                            <?php } ?>
                        <?php } ?>
                        </select>
                    <?php } else if($pageState == "amend_slip_out_tax") { ?>
                        <select name="storageCode" id="storageCode" onchange="getSJT()" readonly>
                        <?php foreach (getAllStorages() as $key) { ?>
                            <?php if($key["storageCode"] == $result["storageCode"]) { ?>
                                <option value="<?php echo $key["storageCode"]; ?>" selected><?php echo $key["storageName"]; ?></option>
                            <?php } else { ?>
                                <option value="<?php echo $key["storageCode"]; ?>"><?php echo $key["storageName"]; ?></option>
                            <?php } ?>
                        <?php } ?>
                        </select>
                    <?php } ?>
                </td>
                <?php if($pageState == "amend_slip_in"){ ?>
                <td>Name Vendor</td>
                <td>:</td>
                <td>
                    <select name="vendorCode" id="vendorCode">
                        <?php foreach (getAllVendors() as $key) { ?>
                            <?php if($key["vendorCode"] == $result["vendorCode"]) { ?>
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
                            <?php if($key["vendorCode"] == $result["vendorCode"]) { ?>
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
            <?php if($pageState == "amend_slip_in"){ ?>
                <td>NO. LPB</td>
                <td>:</td>
                <td colspan="2">
                    <input name="no_lpb_display" type="text" id="no_lpb_display" placeholder="Otomatis dari sistem" value="<?php echo $result["no_LPB"]; ?>" readonly>
                    <input name="no_LPB" type="hidden" id="no_LPB" value="<?php echo $result["no_LPB"]; ?>">
                </td>
            <?php } else { ?>
                <td>No SJ</td>
                <td>:</td>
                <td colspan="2"><input name="no_sj" type="text" id="no_sj" placeholder="di isi" value="<?php echo $result["nomor_surat_jalan"]; ?>" readonly></td>
            <?php } ?>
                <td>Tgl Penerimaan</td>
                <td>:</td>
                <td>
                <?php if($pageState == "amend_slip_in"){ ?>
                    <input name="order_date" type="date" id="tgl_penerimaan" value="<?php echo $result["order_date"]; ?>" onchange="getLPB()" placeholder="di isi" required>
                <?php } else if($pageState == "amend_slip_in") { ?>
                    <input name="order_date" type="date" id="tgl_penerimaan" value="<?php echo $result["order_date"]; ?>" onchange="getSJ()" placeholder="di isi" required>
                <?php } else { ?>
                    <input name="order_date" type="date" id="tgl_penerimaan" value="<?php echo $result["order_date"]; ?>" onchange="getSJT()" placeholder="di isi" required>
                <?php } ?>
                </td>
            </tr>
            <tr class="highlight">
            <?php if($pageState == "amend_slip_in"){ ?>
                <td>No SJ</td>
                <td>:</td>
                <td colspan="2"><input name="no_sj" type="text" id="no_sj" placeholder="di isi" value="<?php echo $result["nomor_surat_jalan"]; ?>" required></td>
            <?php } else { ?>
                <td>No Truk</td>
                <td>:</td>
                <td colspan="2"><input name="no_truk" type="text" id="no_truk" placeholder="di isi" value="<?php echo $result["no_truk"]; ?>" required></td>
            <?php } ?>
                <td>No PO</td>
                <td>:</td>
                <td><input name="purchase_order" type="text" id="purchase_order" placeholder="di isi" value="<?php echo $result["purchase_order"]; ?>" required></td>
            </tr>
            <tr>
            <?php if($pageState == "amend_slip_in"){ ?>
                <td>No Truk</td>
                <td>:</td>
                <td colspan="2"><input name="no_truk" type="text" id="no_truk" placeholder="di isi" value="<?php echo $result["no_truk"]; ?>" required></td>
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
            <?php
            $count = 1;
            foreach($products as $key){ ?>
            <tr>
                <td><?php echo $count++; ?></td>
                <td><input type="text" name="kd[]" placeholder="di isi" class="productCode" oninput="applyAutocomplete(this)" value="<?php echo $key["productCode"]; ?>" required></td>
                <td><input style="width: 300px;" type="text" name="material_display[]" value="<?php echo $key["productName"]; ?>" readonly><input type="hidden" name="material[]"></td>
                <td><input type="number" name="qty[]" placeholder="di isi" value="<?php echo $key["qty"]; ?>" required></td>
                <td><input type="text" name="uom[]" placeholder="di isi" value="<?php echo $key["uom"]; ?>" required></td>
                <td><input type="text" name="note[]" value="<?php echo $key["note"]; ?>" placeholder=""></td>
                <td><button class="btn btn-danger" onclick="deleteRow(this)">Delete</button></td>
            </tr>
            <?php } ?>
            </tbody>
        </table>
        <button type="button" class="btn btn-success" onclick="addRow()">Add Row</button>
        <button type="submit" class="btn btn-outline-success">Submit</button>
    </form>
</main>

<script>

</script>
<script src="../js/index.js" async defer></script>

<?php include "footer.php"; ?>
