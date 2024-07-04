<?php 

session_start();
require_once "../model/user_functions.php";
require_once "../model/storage_functions.php";
require_once "../model/invoice_functions.php";
require_once "../model/payment_functions.php";
require_once "../model/vendor_functions.php";
require_once "../model/order_functions.php";
require_once "../model/product_functions.php";
require_once "../model/order_products_functions.php";
require_once "../fpdf/fpdf.php";

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

    case "getOrderProducts":
        $no_sj = filter_input(INPUT_GET, 'no_sj', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $status = filter_input(INPUT_GET, 'status', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        echo json_encode(getOrderProductsFromNoID($no_sj, $status));
        break;

    case "getOrderByNoSJ":
        $no_sj = filter_input(INPUT_GET, 'no_sj', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $status = filter_input(INPUT_GET, 'status', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        echo json_encode(getOrderByNoSJ($no_sj, $status));
        break;

    case "getInvoiceByNoSJ":
        $no_sj = filter_input(INPUT_GET, 'no_sj', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        echo json_encode(getInvoiceByNoSJ($no_sj));
        break;

    case "create_slip":
        $storageCode = filter_input(INPUT_POST, "storageCode", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $no_LPB = filter_input(INPUT_POST, "no_LPB", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $no_sj = filter_input(INPUT_POST, "no_sj", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $no_truk = filter_input(INPUT_POST, "no_truk", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $vendorCode = filter_input(INPUT_POST, "vendorCode", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $order_date = filter_input(INPUT_POST, "order_date", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $purchase_order = filter_input(INPUT_POST, "purchase_order", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $productCodes = filter_input_array(INPUT_POST)["kd"];
        $qtys = filter_input_array(INPUT_POST)["qty"];
        $uoms = filter_input_array(INPUT_POST)["uom"];
        $notes = filter_input_array(INPUT_POST)["note"];

        create_slip($no_sj, $storageCode, $no_LPB, $no_truk, $vendorCode, $order_date, $purchase_order, 1);

        for($i = 0; $i < count($productCodes); $i++){
            addOrderProducts($no_sj, $productCodes[$i], $qtys[$i], $uoms[$i], $notes[$i], "in");
        }

        header("Location:../controller/index.php?action=dashboard");
        break;

    case "create_invoice":
        $storageCode = filter_input(INPUT_POST, "storageCode", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $no_LPB = filter_input(INPUT_POST, "no_LPB", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $no_sj = filter_input(INPUT_POST, "no_sj", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $no_truk = filter_input(INPUT_POST, "no_truk", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $vendorCode = filter_input(INPUT_POST, "vendorCode", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $purchase_order = filter_input(INPUT_POST, "purchase_order", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $invoice_date = filter_input(INPUT_POST, "invoice_date", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $no_invoice = filter_input(INPUT_POST, "no_invoice", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $no_faktur = filter_input(INPUT_POST, "no_faktur", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $productCodes = filter_input_array(INPUT_POST)["kd"];
        $productNames = filter_input_array(INPUT_POST)["material"];
        $qtys = filter_input_array(INPUT_POST)["qty"];
        $uoms = filter_input_array(INPUT_POST)["uom"];
        $price_per_uom = filter_input_array(INPUT_POST)["price_per_uom"];

        $total_amount = 0;
        $taxPPN = 0;
        $pay_amount = 0;

        $storageName = getstorageByCode($storageCode)["storageName"];
        $vendorName = getVendorByCode($vendorCode)["vendorName"];

        // create_invoice($no_sj, $invoice_date, $no_invoice, $no_faktur);

        // for($i = 0; $i < count($productCodes); $i++){
        //     updatePriceForProducts($no_sj, $productCodes[$i], $price_per_uom[$i]);
        // }
        header("Location:../controller/index.php?action=dashboard");

        // Create instance of FPDF
        $pdf = new FPDF('L', 'mm', 'A5');
        $pdf->AddPage();

        // Set font
        $pdf->SetFont('Arial', 'B', 12);

        // Header
        $pdf->Cell(130, 10, 'INVOICE IN', 0, 1, 'C');

        // PT and Vendor details
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(30, 10, 'PT', 0, 0);
        $pdf->Cell(5, 10, ':', 0, 0);
        $pdf->Cell(80, 10, $storageName, 0, 0);
        $pdf->Cell(30, 10, 'Name Vendor', 0, 0);
        $pdf->Cell(5, 10, ':', 0, 0);
        $pdf->Cell(40, 10, $vendorName, 0, 1);

        // Second row of details
        $pdf->Cell(30, 10, 'NO. SJ', 0, 0);
        $pdf->Cell(5, 10, ':', 0, 0);
        $pdf->Cell(80, 10, $no_sj, 0, 0);
        $pdf->Cell(30, 10, 'No PO', 0, 0);
        $pdf->Cell(5, 10, ':', 0, 0);
        $pdf->Cell(40, 10, $purchase_order, 0, 1);

        // Third row of details
        $pdf->Cell(30, 10, 'No Truk', 0, 0);
        $pdf->Cell(5, 10, ':', 0, 0);
        $pdf->Cell(80, 10, $no_truk, 0, 0);
        $pdf->Cell(30, 10, 'Tgl Invoice', 0, 0);
        $pdf->Cell(5, 10, ':', 0, 0);
        $pdf->Cell(40, 10, $invoice_date, 0, 1);

        // Fourth row of details
        $pdf->Cell(30, 10, 'NO. LPB', 0, 0);
        $pdf->Cell(5, 10, ':', 0, 0);
        $pdf->Cell(80, 10, $no_LPB, 0, 0);
        $pdf->Cell(30, 10, 'NO.Invoice Vendor', 0, 0);
        $pdf->Cell(5, 10, ':', 0, 0);
        $pdf->Cell(40, 10, $no_invoice, 0, 1);

        // Add product table
        $pdf->Ln(1);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(10, 10, 'No', 1);
        $pdf->Cell(30, 10, 'KD', 1);
        $pdf->Cell(50, 10, 'Material', 1);
        $pdf->Cell(20, 10, 'QTY', 1);
        $pdf->Cell(20, 10, 'UOM', 1);
        $pdf->Cell(30, 10, 'Price/uom', 1);
        $pdf->Cell(30, 10, 'Nominal', 1);
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 10);
        for($i = 0; $i < count($productCodes); $i++){
            $pdf->Cell(10, 10, $i, 1);
            $pdf->Cell(30, 10, $productCodes[$i], 1);
            $pdf->Cell(50, 10, $productNames[$i], 1);
            $pdf->Cell(20, 10, $qtys[$i], 1);
            $pdf->Cell(20, 10, $uoms[$i], 1);
            $pdf->Cell(30, 10, $price_per_uom[$i], 1);
            $pdf->Cell(30, 10, ($qtys[$i] * $price_per_uom[$i]), 1);
            $total_amount += ($qtys[$i] * $price_per_uom[$i]);
            $pdf->Ln();
        }

        $taxPPN = $total_amount * 0.11;
        $pay_amount = $total_amount + $taxPPN;

        // Footer
        $pdf->Ln(1);
        $pdf->Cell(30, 10, 'NO. Faktur', 0, 0);
        $pdf->Cell(70, 10, ': ' . $no_faktur, 0, 0);
        $pdf->Cell(30, 10, 'Total Nilai Barang', 0, 0);
        $pdf->Cell(30, 10, ': ' . $total_amount, 0, 1);

        $pdf->Cell(100, 10, '', 0, 0);
        $pdf->Cell(30, 10, 'PPN 11%', 0, 0);
        $pdf->Cell(30, 10, ': ' . $taxPPN, 0, 1);

        $pdf->Cell(100, 10, '', 0, 0);
        $pdf->Cell(40, 10, 'NIlai Yg Harus Dibayar', 0, 0);
        $pdf->Cell(30, 10, ': ' . $pay_amount, 0, 1);

        // Output the PDF
        header('Content-Type: application/pdf');
        $pdf->Output('I', 'invoice.pdf');
        break;

    case "create_payment":
        $no_sj = filter_input(INPUT_POST, "no_sj", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $payment_date = filter_input(INPUT_POST, "payment_date", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $payment_amount = filter_input(INPUT_POST, "payment_amount", FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        create_payment($no_sj, $payment_date, $payment_amount);

        header("Location:../controller/index.php?action=dashboard");
        break;

    case "test":
        var_dump(getstorageByCode("APA")["storageName"]);
}


?>