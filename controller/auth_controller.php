<?php
// controller/auth_controller.php
switch($action){
    case "dashboard":
        if($userID == null){
            header("Location:../controller/index.php?action=show_login");
        }
        $title = "dashboard";
        require_once "../view/dashboard.php";
        break;

    case "show_login":
        if(isset($_SESSION["userID"])){
            header("Location:../controller/index.php?action=dashboard");
        }
        $title = "login";
        require_once "../view/login.php";
        break;

    case "login":
        if(isset($_SESSION["userID"])){
            header("Location:../controller/index.php?action=dashboard");
        }
        $username_inp = filter_input(INPUT_POST, "username", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $password_inp = filter_input(INPUT_POST, "password", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        login($username_inp, $password_inp);

        header("Location:../controller/index.php?action=dashboard");
        break;

    case "logout":
        logout();
        header("Location:../controller/index.php?action=show_login");
        break;
}
?>