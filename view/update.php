<?php include "header.php"; ?>

<main>
    <h1>Update <?php echo htmlspecialchars($data); ?></h1>
    <form action="<?php echo "../controller/index.php?action=master_update_data&data=" . urlencode($data); ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="data" value="<?php echo htmlspecialchars($data); ?>">
        <input type="hidden" name="oldCode" value="<?php echo htmlspecialchars($result[$keyNames[0]]); ?>">
        <?php for($i = 0; $i < count($keyNames); $i++){ ?>
            <label><?php echo htmlspecialchars($keyNames[$i]); ?>: </label>
            <input type="text" name="input_data[]" value="<?php echo htmlspecialchars($result[$keyNames[$i]]); ?>">
            <br>
        <?php } ?>
        <?php if($data == "storage"){ ?>
            <label>logo file: </label>
            <input type="file" name="logo">
            <br>
        <?php } ?>
        <button type="submit" class="btn btn-success">Update</button>
    </form>
</main>

<?php include "footer.php"; ?>
