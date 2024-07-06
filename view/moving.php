<?php include "header.php"; ?>

<main class="main-container">
<form id="myForm" action="../controller/index.php?action=create_repack" method="post">
    <h1 style="text-align:center;">SLIP MOVING BARANG</h1>
    <table class="header-table">
        <tr>
            <td>PT Pengirim</td>
            <td>:</td>
            <td>
            <select name="storageCodeSender" id="storageCodeSender" onchange="getRepackNO()" readonly>
                <?php foreach (getAllStorages() as $key) { ?>
                    <?php if($key["storageCode"] == "NON") { ?>
                        <option value="<?php echo $key["storageCode"]; ?>" selected><?php echo $key["storageName"]; ?></option>
                    <?php } else { ?>
                        <option value="<?php echo $key["storageCode"]; ?>"><?php echo $key["storageName"]; ?></option>
                    <?php } ?>
                <?php } ?>
            </select>
            </td>
            <td>PT Penerima</td>
            <td>:</td>
            <td>
            <select name="storageCodeSender" id="storageCodeSender" onchange="getRepackNO()" readonly>
                <?php foreach (getAllStorages() as $key) { ?>
                    <?php if($key["storageCode"] == "NON") { ?>
                        <option value="<?php echo $key["storageCode"]; ?>" selected><?php echo $key["storageName"]; ?></option>
                    <?php } else { ?>
                        <option value="<?php echo $key["storageCode"]; ?>"><?php echo $key["storageName"]; ?></option>
                    <?php } ?>
                <?php } ?>
            </select>
            </td>
        </tr>
        <tr>
            <td>NO. moving</td>
            <td>:</td>
            <td><input name="no_moving" id="no_moving" type="text" readonly></td>
            <td>Tgl. moving</td>
            <td>:</td>
            <td><input name="moving_date" id="moving_date" type="date" required></td>
        </tr>
    </table>

    <table id="materialTable">
        <thead>
            <tr>
                <th>No</th>
                <th>KD</th>
                <th>Material</th>
                <th>QTY</th>
                <th>UOM</th>
                <th>price/UOM</th>
                <th>action</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td><input name="productCode[]" class="productCode" oninput="applyAutocomplete(this)" type="text" placeholder="di isi" required/></td>
                <td><input name="productName[]" type="text" placeholder="Otomatis" readonly/></td>
                <td><input name="qty[]" type="text" placeholder="di isi"required /></td>
                <td><input name="uom[]" type="text" placeholder="di isi" required/></td>
                <td><input name="price_per_uom[]" type="text" placeholder="di isi" required/></td>
                <td><button type="button" class="btn btn-danger" onclick="removeRow(this)">Remove</button></td>
            </tr>
        </tbody>
    </table>
    <p>
        <span class="add-row" onclick="addRow('materialTable')">Add Row</span>
    </p>

    <button type="submit" class="btn btn-outline-success">Submit</button>
</form>
</main>

<script>
    window.onload = function() {
        getMovingNO();
    };
</script>
<script src="../js/moving.js"></script>

<?php include "footer.php"; ?>