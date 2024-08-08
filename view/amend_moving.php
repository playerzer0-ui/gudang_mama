<?php include "header.php"; ?>

<main class="main-container">
<form id="myForm" action="../controller/index.php?action=create_moving" method="post">
    <h1 style="text-align:center;">AMEND MOVING BARANG</h1>
    <input type="hidden" id="pageState" name="pageState" value=<?php echo $pageState; ?>>
    <table class="header-table">
        <tr>
            <td>PT Pengirim</td>
            <td>:</td>
            <td>
            <select name="storageCodeSender" id="storageCodeSender" onchange="getMovingNO()" readonly>
                <?php foreach (getAllStorages() as $key) { ?>
                    <?php if($key["storageCode"] == $result["storageCodeSender"]) { ?>
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
            <select name="storageCodeReceiver" id="storageCodeReceiver" readonly>
                <?php foreach (getAllStorages() as $key) { ?>
                    <?php if($key["storageCode"] == $result["storageCodeReceiver"]) { ?>
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
            <td><input name="no_moving" id="no_moving" type="text" value="<?php echo $result["no_moving"]; ?>" readonly></td>
            <td>Tgl. moving</td>
            <td>:</td>
            <td><input name="moving_date" id="moving_date" onchange="getMovingNO()" type="date" value="<?php echo $result["moving_date"]; ?>" required></td>
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
                <th>nominal</th>
                <th>action</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td><input name="kd[]" class="productCode" oninput="applyAutocomplete(this)" type="text" placeholder="di isi" required/></td>
                <td><input name="productName[]" type="text" placeholder="Otomatis" readonly/></td>
                <td><input name="qty[]" type="text" placeholder="di isi" oninput="calculateNominal(this)" required/></td>
                <td><input name="uom[]" type="text" placeholder="di isi" required/></td>
                <td><input name="price_per_uom[]" type="text" placeholder="otomatis" readonly/></td>
                <td><input type="number" inputmode="numeric" name="nominal[]" placeholder="Otomatis" readonly></td>
                <td><button type="button" class="btn btn-danger" onclick="removeRow(this)">Remove</button></td>
            </tr>
        </tbody>
    </table>
    <p>
        <span class="add-row" onclick="addRow('materialTable')"><button type="button" class="btn btn-success">add row</button></span>
        <button type="submit" class="btn btn-outline-success">Submit</button>
    </p>

</form>
</main>

<script>
    window.onload = function() {
        getMovingNO();
    };
</script>
<script src="../js/moving.js"></script>

<?php include "footer.php"; ?>