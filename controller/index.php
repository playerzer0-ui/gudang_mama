<?php
// index.php - Main controller
ini_set('session.use_strict_mode', '1');
session_set_cookie_params([
    'httponly' => true,
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'samesite' => 'Lax',
]);
session_start();
require_once __DIR__ . '/auth_helpers.php';
require_once "universal_functions.php";

$action = filter_input(INPUT_GET, "action");
if($action == null){
    $action = "show_login";
}

// A password-only or pre-upgrade session cannot access warehouse actions.
try {
    gm_guard($action);
} catch (Throwable $error) {
    gm_auth_error();
}

// Check user access
if (!checkAccess($action, $userType)) {
    header("Location:../controller/index.php?action=dashboard");
    exit;
}

// These authenticated page visits are separate from generating filtered results.
$viewPages = ['dashboard' => 'storage', 'show_hutang' => 'debts', 'show_piutang' => 'receivables'];
if (isset($viewPages[$action])) {
    require_once "../model/users_action_functions.php";
    try {
        recordReportView($viewPages[$action], $action);
    } catch (Throwable $error) {
        error_log('VIEW logging: ' . $error->getMessage());
        http_response_code(500);
        exit('Unable to record this visit. Please try again.');
    }
}

// Route to appropriate controller
switch($action){
    case "dashboard":
    case "show_login":
    case "login":
    case "logout":
    case "totp_setup":
    case "totp_confirm":
    case "totp_challenge":
    case "totp_verify":
    case "totp_recovery_codes":
    case "totp_acknowledge":
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