<?php include "header.php"; ?>

    <main class="main-container">
        <div class="form-container bg-white">
        <form action="../controller/index.php?action=register" method="post">
            <h1>REGISTER</h1>
            <div class="mb-3">
                <label for="exampleInputEmail1" class="form-label">username</label>
                <input type="text" name="username" class="form-control" id="exampleInputUsername1" aria-describedby="usernameHelp">
            </div>
            <div class="mb-3">
                <label for="exampleInputPassword1" class="form-label">Password</label>
                <input type="password" name="password" class="form-control" id="exampleInputPassword1">
            </div>
            <div class="mb-3">
                <label for="exampleInputPassword1" class="form-label">user type</label>
                <select name="userType" class="form-select">
                    <option value="0">normal user</option>
                    <option value="1">admin user</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">register new user</button>
        </form>
        </div>
    </main>
        
        
<?php include "footer.php"; ?>