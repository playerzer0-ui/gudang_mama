<?php include "header.php"; ?>

<main>
    <div>
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
        <table id="reporttable" border="1">
            <!-- JavaScript will populate this table -->
        </table>
    </div>
</main>

<script src="../js/piutang.js"></script>

<?php include "footer.php"; ?>