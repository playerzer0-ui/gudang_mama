<?php
// controller/universal_functions.php
require_once "../model/user_functions.php";
require_once "../model/storage_functions.php";
require_once "../model/invoice_functions.php";
require_once "../model/payment_functions.php";
require_once "../model/vendor_functions.php";
require_once "../model/customer_functions.php";
require_once "../model/order_functions.php";
require_once "../model/product_functions.php";
require_once "../model/repack_functions.php";
require_once "../model/moving_functions.php";
require_once "../model/saldo_functions.php";
require_once "../model/utility_functions.php";
require_once "../model/order_products_functions.php";
require_once "../fpdf/fpdf.php";
require_once "../model/pdf_creation.php";
require_once "../model/excel_creation.php";

global $db;

// Initialize global variables
$title = "";
$pageState = "";
$msg = filter_input(INPUT_GET, "msg", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
if($msg == null){
    $msg = "";
}

// User session handling
if(isset($_SESSION["userID"])){
    $userID = $_SESSION["userID"];
    $username = $_SESSION["username"];
    $userType = $_SESSION["userType"];
    $logState = "logout";
}
else{
    $userID = null;
    $username = "";
    $userType = 0;
    $logState = "login";
}
?>