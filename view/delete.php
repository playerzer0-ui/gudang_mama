<?php include "header.php"; ?>

<main>
    <h1>CONFIRM DELETE <span style="color: red;"><?php echo strtoupper($code); ?></span>?</h1>
    <form action="../controller/index.php?action=master_delete_data" method="post">
        <input type="hidden" name="data" value="<?php echo htmlspecialchars($data); ?>">
        <input type="hidden" name="code" value="<?php echo htmlspecialchars($code); ?>">
        <p>this data will no longer exist on the master table, if there are any orders linked to this data, it won't delete and send an error instead</p>
        <button type="submit" class="btn btn-danger">DELETE THIS DATA</button>
    </form>
</main>

<?php include "footer.php"; ?>