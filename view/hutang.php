<?php include "header.php"; ?>

<?php $reportKind = 'hutang'; $reportTitle = 'Hutang report'; $reportResultTitle = 'Vendor invoices'; include 'report_top.php'; ?>

        <table id="reporttable" class="gm-report-table">
            <!-- JavaScript will populate this table -->
        </table>
    <?php include 'report_bottom.php'; ?>

<script src="../js/hutang.js"></script>

<?php include "footer.php"; ?>
