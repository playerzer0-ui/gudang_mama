<?php include "header.php"; ?>

<main>
    <h1>create <?php echo $data; ?></h1>
    <form action=<?php echo "../controller/index.php?action=master_create_data&data=" . $data; ?> method="post" enctype="multipart/form-data">
        <input type="hidden" name="data" value=<?php echo $data; ?>>
        <?php for($i = 0; $i < count($keyNames); $i++){ ?>
            <label><?php echo $keyNames[$i]; ?>: </label>
            <input type="text" name="input_data[]">
            <br>
        <?php } ?>
        <?php if($data == "storage"){ ?>
            <label>logo file: </label>
            <input type="file" name="logo">
            <br>
        <?php } ?>
        <button type="submit" class="btn btn-success">submit</button>
    </form>
</main>

<?php include "footer.php"; ?>