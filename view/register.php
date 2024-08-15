<?php include "header.php"; ?>
<?php

if(!isset($result["userID"])){
    $username = "";
}
else{
    $username = $result["username"];
}

?>

    <main class="main-container">
        <div class="form-container bg-white">
        <?php if($action == "master_create"){ ?>
        <form action="../controller/index.php?action=master_create_data" method="post">
            <h1>REGISTER</h1>
        <?php } else { ?>
        <form action="../controller/index.php?action=master_update_data" method="post">
            <h1>UPDATE</h1>
        <?php } ?>
            <input type="hidden" name="data" value="users">
            <input type="hidden" name="oldCode" value="<?php echo $username; ?>">
            <div class="mb-3">
                <label for="exampleInputEmail1" class="form-label">username</label>
                <input type="text" name="input_data[]" class="form-control" id="exampleInputUsername1" aria-describedby="usernameHelp" value="<?php echo $username; ?>" required>
            </div>
            <div class="mb-3">
                <label for="exampleInputPassword1" class="form-label">Password</label>
                <input type="password" name="input_data[]" class="form-control" id="exampleInputPassword1" required>
            </div>
            <div class="mb-3">
                <label for="exampleInputPassword1" class="form-label">user type</label>
                <select name="input_data[]" class="form-select" required>
                    <option value="0">normal user</option>
                    <option value="1">admin user</option>
                </select>
            </div>
            <?php if($action == "master_create"){ ?>
                <button type="submit" class="btn btn-primary">register new user</button>
            <?php } else { ?>
                <button type="submit" class="btn btn-primary">update user</button>
            <?php } ?>
        </form>
        </div>
    </main>
        
        
<?php include "footer.php"; ?>