<?php include "header.php"; ?>

 <main>
    <div>
    <label for="storageCode">storage:</label>
    <select name="storageCode" id="storageCode">
        <?php foreach (getAllStorages() as $key) { ?>
            <?php if($key["storageCode"] == "NON") { ?>
                <option value="<?php echo $key["storageCode"]; ?>" selected><?php echo $key["storageName"]; ?></option>
            <?php } else { ?>
                <option value="<?php echo $key["storageCode"]; ?>"><?php echo $key["storageName"]; ?></option>
            <?php } ?>
        <?php } ?>
    </select>
    <label for="month">Month:</label>
    <select id="month" name="month">
        <option value="01">January</option>
        <option value="02">February</option>
        <option value="03">March</option>
        <option value="04">April</option>
        <option value="05">May</option>
        <option value="06">June</option>
        <option value="07">July</option>
        <option value="08">August</option>
        <option value="09">September</option>
        <option value="10">October</option>
        <option value="11">November</option>
        <option value="12">December</option>
    </select>

    <label for="year">Year:</label>
    <select id="year" name="year">
        <!-- JavaScript will populate the year options -->
    </select>
    <button class="btn btn-secondary" onclick="generateReport()">search</button>
    </div>
    <div>
        <table id="reporttable">
            <thead>
                <tr>
                    <th rowspan="3">No</th>
                    <th rowspan="3">KD</th>
                    <th rowspan="3">Material</th>
                    <th colspan="3">Saldo Awal</th>
                    <th colspan="9">Penerimaan</th>
                    <th colspan="3">BARANG SIAP DI JUAL</th>
                    <th colspan="9">Pengeluaran</th>
                    <th colspan="3">Saldo Akhir</th>
                </tr>
                <tr>
                    <th rowspan="2">QTY</th>
                    <th rowspan="2">H/QTY</th>
                    <th rowspan="2">Rupiah</th>
                    <th colspan="3" style="background-color: #FFFF00;">Pembelian</th>
                    <th colspan="3" style="background-color: #FFC000;">Pindah PT</th>
                    <th colspan="3" style="background-color: #FFC000;">Repack</th>
                    <th rowspan="2">QTY</th>
                    <th rowspan="2">H/QTY</th>
                    <th rowspan="2" style="background-color: #FFFF00;">Rupiah</th>
                    <th colspan="3" style="background-color: #92D050;">Penjualan</th>
                    <th colspan="3" style="background-color: #FFFF00;">Pindah PT</th>
                    <th colspan="3" style="background-color: #FF0000;">Repack</th>
                    <th rowspan="2">QTY</th>
                    <th rowspan="2">H/QTY</th>
                    <th rowspan="2">Rupiah</th>
                </tr>
                <tr>
                    <th>QTY</th>
                    <th>H/QTY</th>
                    <th>Rupiah</th>
                    <th>QTY</th>
                    <th>H/QTY</th>
                    <th>Rupiah</th>
                    <th>QTY</th>
                    <th>H/QTY</th>
                    <th>Rupiah</th>
                    <th>QTY</th>
                    <th>H/QTY</th>
                    <th>Rupiah</th>
                    <th>QTY</th>
                    <th>H/QTY</th>
                    <th>Rupiah</th>
                    <th>QTY</th>
                    <th>H/QTY</th>
                    <th>Rupiah</th>
                </tr>
            </thead>
        <tbody>
            <!-- Data rows go here -->
        </tbody>
    </table>

    </div>
 </main>

 <script src="../js/storage.js"></script>

<?php include "footer.php"; ?>