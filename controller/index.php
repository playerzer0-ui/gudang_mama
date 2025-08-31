<?php
// index.php - Main controller
session_start();
require_once "universal_functions.php";

$action = filter_input(INPUT_GET, "action");
if($action == null){
    $action = "show_login";
}

// Check user access
if (!checkAccess($action, $userType)) {
    header("Location:../controller/index.php?action=dashboard");
    exit;
}

// Route to appropriate controller
switch($action){
    case "dashboard":
    case "show_login":
    case "login":
    case "logout":
        require_once "auth_controller.php";
        break;
        
    case "show_slip":
    case "show_invoice":
    case "show_payment":
    case "show_repack":
    case "show_moving":
    case "show_hutang":
    case "show_piutang":
    case "show_amends":
        require_once "page_controller.php";
        break;
        
    case "master_read":
    case "master_create":
    case "master_create_data":
    case "master_update":
    case "master_update_data":
    case "master_delete":
    case "master_delete_data":
        require_once "master_data_controller.php";
        break;
        
    case "amend_update":
    case "amend_update_data":
    case "amend_delete_data":
    case "amendDelete":
        require_once "amend_controller.php";
        break;
        
    case "generate_LPB":
    case "generate_SJ":
    case "generate_SJT":
    case "generate_SJR":
    case "generate_SJP":
    case "generateNoInvoice":
        require_once "generator_controller.php"; // This was missing!
        break;
        
    case "getProductSuggestions":
    case "getProductDetails":
    case "getMovingDetails":
    case "getOrderProducts":
    case "getOrderByNoSJ":
    case "getInvoiceByNoSJ":
    case "getInvoiceMovingByNoSJ":
        require_once "ajax_controller.php";
        break;
        
    case "create_slip":
    case "create_invoice":
    case "create_payment":
    case "create_repack":
    case "create_moving":
        require_once "create_controller.php";
        break;
        
    case "getHPP":
    case "getLaporanHutang":
    case "getLaporanPiutang":
    case "getReportStock":
    case "calculateHutang":
        require_once "report_controller.php";
        break;
        
    case "create_pdf":
    case "excel_stock":
    case "excel_hutang":
    case "excel_piutang":
    case "getLogs":
        require_once "export_controller.php";
        break;
        
    default:
        header("Location:../controller/index.php?action=show_login");
        break;
}
?>