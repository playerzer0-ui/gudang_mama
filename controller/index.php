<?php 

session_start();
require_once "../model/user_functions.php";
require_once "../model/storage_functions.php";
require_once "../model/vendor_functions.php";
require_once "../model/order_functions.php";
require_once "../model/product_functions.php";
require_once "../model/order_products_functions.php";

$action = filter_input(INPUT_GET, "action");
$title = "";
$pageState = "";
if($action == null){
    $action = "show_login";
}

if(isset($_SESSION["userID"])){
    $userID = $_SESSION["userID"];
    $username = $_SESSION["username"];
    $userType = $_SESSION["userType"];
    $state = "logout";
}
else{
    $userID = null;
    $username = "";
    $userType = 0;
    $state = "login";
}

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

    case "show_register":
        $title = "register";
        require_once "../view/register.php";
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

    case "register":
        $username_inp = filter_input(INPUT_POST, "username", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $password_inp = filter_input(INPUT_POST, "password", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $usertype_inp = filter_input(INPUT_POST, "userType", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        register($username_inp, $password_inp, $usertype_inp);

        header("Location:../controller/index.php?action=dashboard");
        break;

    case "logout":
        logout();
        header("Location:../controller/index.php?action=show_login");
        break;

    case "show_slip":
        $pageState = filter_input(INPUT_GET, "state", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $title = "slip " . $pageState;
        require_once "../view/slip.php";
        break;

    case "show_invoice":
        $pageState = filter_input(INPUT_GET, "state", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $title = "invoice " . $pageState;
        require_once "../view/invoice.php";
        break;

    case "show_payment":
        $pageState = filter_input(INPUT_GET, "state", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $title = "payment " . $pageState;
        require_once "../view/payment.php";
        break;

    case "generate_LPB":
        $storageCode = filter_input(INPUT_GET, "storageCode", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        echo generateNoLPB($storageCode, "1");
        break;
    
    case 'getProductSuggestions':
        $term = filter_input(INPUT_GET, 'term', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        echo json_encode(getProductSuggestions($term));
        break;
    case 'getProductDetails':
        $productCode = filter_input(INPUT_GET, 'productCode', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        echo json_encode(getProductByCode($productCode));
        break;
}


?>