<?php include "header.php"; ?>

 <?php $reportKind = 'storage'; $reportTitle = 'Storage report'; $reportDescription = 'Inventory balances and movement by period.'; $reportResultTitle = 'Inventory movement'; include 'report_top.php'; ?>

        <?php if($userType == 1){ ?>
            <table id="reporttable" class="gm-report-table">
            <thead>
                <tr>
                    <th rowspan="3">No</th>
                    <th rowspan="3">KD</th>
                    <th rowspan="3">Material</th>
                    <th colspan="3">Saldo Awal</th>
                    <th colspan="12">Penerimaan</th>
                    <th colspan="3">Barang Siap Dijual</th>
                    <th colspan="12">Pengeluaran</th>
                    <th colspan="3">Saldo Akhir</th>
                </tr>
                <tr>
                    <th rowspan="2">QTY</th>
                    <th rowspan="2">H/QTY</th>
                    <th rowspan="2">Rupiah</th>
                    <th colspan="3">Pembelian</th>
                    <th colspan="3">Pindah PT</th>
                    <th colspan="3">Repack</th>
                    <th colspan="3">Total In</th>
                    <th rowspan="2">QTY</th>
                    <th rowspan="2">H/QTY</th>
                    <th rowspan="2">Rupiah</th>
                    <th colspan="3">Penjualan</th>
                    <th colspan="3">Pindah PT</th>
                    <th colspan="3">Repack</th>
                    <th colspan="3">Total Out</th>
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
                    <th>QTY</th>
                    <th>H/QTY</th>
                    <th>Rupiah</th>
                    <th>QTY</th>
                    <th>H/QTY</th>
                    <th>Rupiah</th>
                </tr>
            </thead>
            <tbody>
                <!-- Data rows will be populated here -->
            </tbody>
        </table>
        <?php } else { ?>
        <table id="reporttable" class="gm-report-table">
            <thead>
                <tr>
                    <th rowspan="3">No</th>
                    <th rowspan="3">KD</th>
                    <th rowspan="3">Material</th>
                    <th colspan="1">Saldo Awal</th>
                    <th colspan="4">Penerimaan</th>
                    <th colspan="1">Barang Siap Dijual</th>
                    <th colspan="4">Pengeluaran</th>
                    <th colspan="1">Saldo Akhir</th>
                </tr>
                <tr>
                    <th rowspan="2">QTY</th>
                    <th colspan="1">Pembelian</th>
                    <th colspan="1">Pindah PT</th>
                    <th colspan="1">Repack</th>
                    <th colspan="1">Total In</th>
                    <th rowspan="2">QTY</th>
                    <th colspan="1">Penjualan</th>
                    <th colspan="1">Pindah PT</th>
                    <th colspan="1">Repack</th>
                    <th colspan="1">Total Out</th>
                    <th rowspan="2">QTY</th>
                </tr>
                <tr>
                    <th>QTY</th>
                    <th>QTY</th>
                    <th>QTY</th>
                    <th>QTY</th>
                    <th>QTY</th>
                    <th>QTY</th>
                    <th>QTY</th>
                    <th>QTY</th>
                </tr>
            </thead>
            <tbody>
                <!-- Data rows will be populated here -->
            </tbody>
        </table>
        <?php } ?>
    <?php include 'report_bottom.php'; ?>

 <script src="../js/storage.js"></script>

<?php include "footer.php"; ?>
