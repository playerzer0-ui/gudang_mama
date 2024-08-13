<?php include "header.php"; ?>

<main>
    <h1>amend <?php echo $state; ?></h1>
    <input type="hidden" id="state" value="<?php echo $state; ?>">
    <div class="container text-center">
        <?php foreach($no_SJs as $key){ ?>
            <div class="row align-items-start">
                <div class="col">
                    <?php if($key["nomor_surat_jalan"] != "-"){ ?>
                        <b><?php echo $key["nomor_surat_jalan"]; ?></b>
                    <?php } else { ?>
                        <b><?php echo $key["no_moving"]; ?></b>
                    <?php } ?>
                </div>
                <div class="col">
                    <?php if($state == "payment"){ ?>
                        <?php if($key["nomor_surat_jalan"] != "-"){ ?>
                            <a href=<?php echo "../controller/index.php?action=amend_update&data=" . $state . "&payment_id=" . $key["payment_id"] . "&code=" . $key["nomor_surat_jalan"]; ?>><button class="btn btn-info">EDIT</button></a>
                            <a href=<?php echo "../controller/index.php?action=master_delete&data=" . $state . "&payment_id=" . $key["payment_id"] . "&code=" . $key["nomor_surat_jalan"]; ?>><button class="btn btn-danger">DELETE</button></a>
                        <?php } else { ?>
                            <a href=<?php echo "../controller/index.php?action=amend_update&data=" . $state . "&payment_id=" . $key["payment_id"] . "&code=" . $key["no_moving"]; ?>><button class="btn btn-info">EDIT</button></a>
                            <a href=<?php echo "../controller/index.php?action=master_delete&data=" . $state . "&payment_id=" . $key["payment_id"] . "&code=" . $key["no_moving"]; ?>><button class="btn btn-danger">DELETE</button></a>
                        <?php } ?>
                    <?php } else { ?>
                        <?php if($key["nomor_surat_jalan"] != "-"){ ?>
                            <a href=<?php echo "../controller/index.php?action=amend_update&data=" . $state . "&code=" . $key["nomor_surat_jalan"]; ?>><button class="btn btn-info">EDIT</button></a>
                            <a href=<?php echo "../controller/index.php?action=master_delete&data=" . $state . "&code=" . $key["nomor_surat_jalan"]; ?>><button class="btn btn-danger">DELETE</button></a>
                        <?php } else { ?>
                            <a href=<?php echo "../controller/index.php?action=amend_update&data=" . $state . "&code=" . $key["no_moving"]; ?>><button class="btn btn-info">EDIT</button></a>
                            <a href=<?php echo "../controller/index.php?action=master_delete&data=" . $state . "&code=" . $key["no_moving"]; ?>><button class="btn btn-danger">DELETE</button></a>
                        <?php } ?>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>
    </div>
</main>

<!-- <script src="../js/amends.js" async defer></script> -->

<?php include "footer.php"; ?>