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
    <div id="excel">
            
    </div>
    </div>
    <div class="table-container">
        <table id="reporttable" border="1">
            <!-- JavaScript will populate this table -->
        </table>
    </div>
</main>

<script src="../js/hutang.js"></script>

<?php include "footer.php"; ?>