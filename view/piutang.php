<?php include "header.php"; ?>

<?php $reportKind = 'piutang'; $reportTitle = 'Piutang report'; $reportResultTitle = 'Customer invoices'; include 'report_top.php'; ?>

        <table id="reporttable" class="gm-report-table">
            <!-- JavaScript will populate this table -->
        </table>
    <?php include 'report_bottom.php'; ?>

<script src="../js/piutang.js"></script>

<?php include "footer.php"; ?>
