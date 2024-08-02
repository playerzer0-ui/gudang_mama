<?php include "header.php"; ?>

<main>
    <h1>amend <?php echo $state; ?></h1>
    <div class="container text-center">
        <?php foreach($no_SJs as $key){ ?>
            <div class="row align-items-start">
                <div class="col">
                    <b><?php echo $key["nomor_surat_jalan"]; ?></b>
                </div>
                <div class="col">
                    <button class="btn btn-info">EDIT</button>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#exampleModal" onclick="setHref(this)" value=<?php echo $key["nomor_surat_jalan"]; ?>>
                    DELETE
                    </button>
                </div>
            </div>
        <?php } ?>
    </div>
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Confirm delete?</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                All assoiciated info will be lost
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a id="deleteButton" href="../controller/index.php?action=test"><button type="button" class="btn btn-danger">delete</button></a>
            </div>
            </div>
        </div>
    </div>

</main>

<script src="../js/amends.js" async defer></script>

<?php include "footer.php"; ?>