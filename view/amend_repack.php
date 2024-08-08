<?php include "header.php"; ?>

<main class="main-container">
<form id="myForm" action="../controller/index.php?action=amend_update_data&data=repack" method="post">
    <h1 style="text-align:center;">AMEND REPACK BARANG</h1>
    <input type="hidden" id="pageState" name="pageState" value=<?php echo $pageState; ?>>
    <table class="header-table">
        <tr>
            <td>PT</td>
            <td>:</td>
            <td>
            <select name="storageCode" id="storageCode" onchange="getRepackNO()" readonly>
                <?php foreach (getAllStorages() as $key) { ?>
                    <?php if($key["storageCode"] == $result["storageCode"]) { ?>
                        <option value="<?php echo $key["storageCode"]; ?>" selected><?php echo $key["storageName"]; ?></option>
                    <?php } else { ?>
                        <option value="<?php echo $key["storageCode"]; ?>"><?php echo $key["storageName"]; ?></option>
                    <?php } ?>
                <?php } ?>
                </select>
            </td>
            <td>Tgl Repack</td>
            <td>:</td>
            <td><input name="repack_date" id="repack_date" onchange="getRepackNO()" value="<?php echo $result["repack_date"]; ?>" type="date" required></td>
        </tr>
        <tr>
            <td>NO. Repack</td>
            <td>:</td>
            <td><input name="no_repack" id="no_repack" type="text" value="<?php echo $result["no_repack"]; ?>" readonly></td>
            <input type="hidden" id="old_rpeack" name="old_repack" value="<?php echo $result["no_repack"]; ?>">
        </tr>
    </table>

    <h3>Material Awal</h3>
    <table id="materialAwalTable">
        <thead>
            <tr>
                <th>No</th>
                <th>KD</th>
                <th>Material Awal</th>
                <th>QTY</th>
                <th>UOM</th>
                <th>Note</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $rowCount = 1;
            foreach($products as $key){
                if($key["product_status"] == "repack_awal"){ ?>
                <tr>
                    <td><?php echo $rowCount++; ?></td>
                    <td><input name="kd_awal[]" class="productCode" oninput="applyAutocomplete(this)" type="text" placeholder="di isi" value="<?php echo $key["productCode"]; ?>" required/></td>
                    <td><input name="material_awal[]" type="text" placeholder="Otomatis" value="<?php echo $key["productName"]; ?>" readonly/></td>
                    <td><input name="qty_awal[]" type="text" placeholder="di isi" value="<?php echo $key["qty"]; ?>" required /></td>
                    <td><input name="uom_awal[]" type="text" placeholder="di isi" value="<?php echo $key["uom"]; ?>" required/></td>
                    <td><input name="note_awal[]" type="text" value="<?php echo $key["note"]; ?>" /></td>
                    <td><button type="button" class="btn btn-danger" onclick="removeRow(this)">Remove</button></td>
                </tr>
            <?php }
            } ?>
        </tbody>
    </table>
    <p>
        <span class="add-row" onclick="addRow('materialAwalTable')">Add Row</span>
    </p>

    <h3>Material Baru</h3>
    <table id="materialBaruTable">
        <thead>
            <tr>
                <th>No</th>
                <th>KD</th>
                <th>Material Baru</th>
                <th>QTY</th>
                <th>UOM</th>
                <th>Note</th>
            </tr>
        </thead>
        <tbody>
        <?php 
        $rowCount = 1;
        foreach($products as $key){
                if($key["product_status"] == "repack_akhir"){ ?>
                <tr>
                    <td><?php echo $rowCount++; ?></td>
                    <td><input name="kd_akhir[]" class="productCode" oninput="applyAutocomplete(this)" type="text" placeholder="di isi" value="<?php echo $key["productCode"]; ?>" required/></td>
                    <td><input name="material_akhir[]" type="text" placeholder="Otomatis" value="<?php echo $key["productName"]; ?>" readonly/></td>
                    <td><input name="qty_akhir[]" type="text" placeholder="di isi" value="<?php echo $key["qty"]; ?>" required /></td>
                    <td><input name="uom_akhir[]" type="text" placeholder="di isi" value="<?php echo $key["uom"]; ?>" required/></td>
                    <td><input name="note_akhir[]" type="text" value="<?php echo $key["note"]; ?>" /></td>
                    <td><button type="button" class="btn btn-danger" onclick="removeRow(this)">Remove</button></td>
                </tr>
            <?php }
            } ?>
        </tbody>
    </table>
    <p>
        <span class="add-row" onclick="addRow('materialBaruTable')">Add Row</span>
    </p>

    <button type="submit" class="btn btn-outline-success">Submit</button>
</form>
</main>

<script>
    window.onload = function() {
        getRepackNO();
    };
</script>
<script src="../js/repack.js"></script>

<?php include "footer.php"; ?>