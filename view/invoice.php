<?php include "header.php"; ?>

<main class="main-container">
    <form action="../controller/index.php?action=create_slip" method="post">
    <h1>INVOICE <?php echo $pageState; ?></h1>
    <table>
        <tr class="form-header">
            <td>PT</td>
            <td>:</td>
            <td colspan="2"><input type="text" id="pt" placeholder="Otomatis dari sistem" disabled></td>
            <td>Name Vendor</td>
            <td>:</td>
            <td><input type="text" id="vendor" placeholder="Otomatis dari sistem" disabled></td>
        </tr>
        <tr>
            <td>NO. LPB</td>
            <td>:</td>
            <td colspan="2"><input type="text" id="no_lpb" placeholder="Otomatis dari sistem" disabled></td>
            <td>No PO</td>
            <td>:</td>
            <td><input type="text" id="no_po" placeholder="Otomatis dari sistem" disabled></td>
        </tr>
        <tr class="highlight">
            <td>No SJ</td>
            <td>:</td>
            <td colspan="2"><input type="text" id="no_sj" placeholder="di isi"></td>
            <td>Tgl invoice</td>
            <td>:</td>
            <td><input type="text" id="no_po" placeholder="di isi"></td>
        </tr>
        <tr>
            <td>No Truk</td>
            <td>:</td>
            <td colspan="2"><input type="text" id="no_truk" placeholder="Otomatis dari sistem" disabled></td>
            <td>no invoice</td>
            <td>:</td>
            <td><input type="text" id="no_po" placeholder="di isi"></td>
        </tr>
    </table>

    </form>
</main>

<?php include "footer.php"; ?>