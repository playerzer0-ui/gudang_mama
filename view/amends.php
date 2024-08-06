<?php include "header.php"; ?>

<main>
    <h1>amend <?php echo $state; ?></h1>
    <input type="hidden" id="state" value="<?php echo $state; ?>">
    <div class="container text-center">
        <?php foreach($no_SJs as $key){ ?>
            <div class="row align-items-start">
                <div class="col">
                    <b><?php echo $key["nomor_surat_jalan"]; ?></b>
                </div>
                <div class="col">
                    <a href=<?php echo "../controller/index.php?action=amend_update&data=" . $state . "&code=" . $key["nomor_surat_jalan"]; ?>><button class="btn btn-info">EDIT</button></a>
                    <a href=<?php echo "../controller/index.php?action=master_delete&data=" . $state . "&code=" . $key["nomor_surat_jalan"]; ?>><button class="btn btn-danger">DELETE</button></a>
                </div>
            </div>
        <?php } ?>
    </div>
</main>

<!-- <script src="../js/amends.js" async defer></script> -->

<?php include "footer.php"; ?>