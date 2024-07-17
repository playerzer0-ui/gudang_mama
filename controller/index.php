<?php 

session_start();
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
require_once "../model/order_products_functions.php";
require_once "../fpdf/fpdf.php";
require_once "../model/pdf_creation.php";

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

    case "show_repack":
        $title = "repack";
        require_once "../view/repack.php";
        break;

    case "show_moving":
        $title = "moving";
        require_once "../view/moving.php";
        break;

    case "show_hutang":
        $title = "hutang";
        require_once "../view/hutang.php";
        break;

    case "show_piutang":
        $title = "piutang";
        require_once "../view/piutang.php";
        break;

    case "generate_LPB":
        $storageCode = filter_input(INPUT_GET, "storageCode", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        echo generateNoLPB($storageCode, "1");
        break;

    case "generate_SJ":
        $storageCode = filter_input(INPUT_GET, "storageCode", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        echo generateNoLPB($storageCode, "2");
        break;

    case "generate_SJT":
        $storageCode = filter_input(INPUT_GET, "storageCode", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        echo generateTaxSJ($storageCode);
        break;

    case "generate_SJR":
        $storageCode = filter_input(INPUT_GET, "storageCode", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        echo generate_SJR($storageCode);
        break;

    case "generate_SJP":
        $storageCode = filter_input(INPUT_GET, "storageCode", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        echo generate_SJP($storageCode);
        break;

    case "generateNoInvoice":
        $storageCode = filter_input(INPUT_GET, "storageCode", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $month = filter_input(INPUT_GET, "month", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $year = filter_input(INPUT_GET, "year", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        echo generateNoInvoice($storageCode, $month, $year);
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
        $customerCode = filter_input(INPUT_POST, "customerCode", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $order_date = filter_input(INPUT_POST, "order_date", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $purchase_order = filter_input(INPUT_POST, "purchase_order", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $productCodes = filter_input_array(INPUT_POST)["kd"];
        $qtys = filter_input_array(INPUT_POST)["qty"];
        $uoms = filter_input_array(INPUT_POST)["uom"];
        $notes = filter_input_array(INPUT_POST)["note"];
        $pageState = filter_input(INPUT_POST, "pageState", FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if($pageState == "in"){
            create_slip($no_sj, $storageCode, $no_LPB, $no_truk, $vendorCode, "NON", $order_date, $purchase_order, 1);
            for($i = 0; $i < count($productCodes); $i++){
                addOrderProducts($no_sj, $productCodes[$i], $qtys[$i], $uoms[$i], $notes[$i], "in");
            }
        }
        else if($pageState == "out"){
            create_slip($no_sj, $storageCode, $no_LPB, $no_truk, "NON", $customerCode, $order_date, $purchase_order, 2);
            for($i = 0; $i < count($productCodes); $i++){
                addOrderProducts($no_sj, $productCodes[$i], $qtys[$i], $uoms[$i], $notes[$i], "out");
            }
        }
        else{
            create_slip($no_sj, $storageCode, $no_LPB, $no_truk, "NON", $customerCode, $order_date, $purchase_order, 3);
            for($i = 0; $i < count($productCodes); $i++){
                addOrderProducts($no_sj, $productCodes[$i], $qtys[$i], $uoms[$i], $notes[$i], "out_tax");
            }
        }

        header("Location:../controller/index.php?action=dashboard");
        break;

    case "create_invoice":
        $storageCode = filter_input(INPUT_POST, "storageCode", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $no_LPB = filter_input(INPUT_POST, "no_LPB", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $no_sj = filter_input(INPUT_POST, "no_sj", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $no_truk = filter_input(INPUT_POST, "no_truk", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $vendorCode = filter_input(INPUT_POST, "vendorCode", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $customerCode = filter_input(INPUT_POST, "customerCode", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $customerAddress = filter_input(INPUT_POST, "customerAddress", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $npwp = filter_input(INPUT_POST, "npwp", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $purchase_order = filter_input(INPUT_POST, "purchase_order", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $invoice_date = filter_input(INPUT_POST, "invoice_date", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $no_invoice = filter_input(INPUT_POST, "no_invoice", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $no_faktur = filter_input(INPUT_POST, "no_faktur", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $productCodes = filter_input_array(INPUT_POST)["kd"];
        $productNames = filter_input_array(INPUT_POST)["material"];
        $qtys = filter_input_array(INPUT_POST)["qty"];
        $uoms = filter_input_array(INPUT_POST)["uom"];
        $price_per_uom = filter_input_array(INPUT_POST)["price_per_uom"];
        $pageState = filter_input(INPUT_POST, "pageState", FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $storageName = getstorageByCode($storageCode)["storageName"];

        if($pageState == "in"){
            $vendorName = getVendorByCode($vendorCode)["vendorName"];
        }
        else{
            $customerName = getCustomerByCode($customerCode)["customerName"];
        }


        create_invoice($no_sj, $invoice_date, $no_invoice, $no_faktur);

        for($i = 0; $i < count($productCodes); $i++){
            updatePriceForProducts($no_sj, $productCodes[$i], $price_per_uom[$i]);
        }

        // Generate the PDF and return it as a response
        if (isset($_POST['generate_pdf'])) {
            if($pageState == "in"){
                create_invoice_in_pdf($storageName, $vendorName, $no_sj, $no_truk, $purchase_order, $invoice_date, $no_LPB, $no_invoice, $productCodes, $productNames, $qtys, $uoms, $price_per_uom, $no_faktur);
            }
            else{
                create_invoice_out_pdf($storageName, $customerCode, $no_sj, $customerAddress, $npwp, $invoice_date, $no_invoice, $productCodes, $productNames, $qtys, $uoms, $price_per_uom, $no_faktur);
            }
            exit;
        }

        // Redirect to the dashboard
        header("Location:../controller/index.php?action=dashboard");
        break;

    case "create_payment":
        $storageCode = filter_input(INPUT_POST, "storageCode", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $no_LPB = filter_input(INPUT_POST, "no_LPB", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $no_sj = filter_input(INPUT_POST, "no_sj", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $no_truk = filter_input(INPUT_POST, "no_truk", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $vendorCode = filter_input(INPUT_POST, "vendorCode", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $customerCode = filter_input(INPUT_POST, "customerCode", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $customerAddress = filter_input(INPUT_POST, "customerAddress", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $npwp = filter_input(INPUT_POST, "npwp", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $purchase_order = filter_input(INPUT_POST, "purchase_order", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $invoice_date = filter_input(INPUT_POST, "invoice_date", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $no_invoice = filter_input(INPUT_POST, "no_invoice", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $payment_date = filter_input(INPUT_POST, "payment_date", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $payment_amount = filter_input(INPUT_POST, "payment_amount", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $productCodes = filter_input_array(INPUT_POST)["kd"];
        $productNames = filter_input_array(INPUT_POST)["material"];
        $qtys = filter_input_array(INPUT_POST)["qty"];
        $uoms = filter_input_array(INPUT_POST)["uom"];
        $price_per_uom = filter_input_array(INPUT_POST)["price_per_uom"];
        $pageState = filter_input(INPUT_POST, "pageState", FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $storageName = getstorageByCode($storageCode)["storageName"];

        if($pageState == "in"){
            $vendorName = getVendorByCode($vendorCode)["vendorName"];
        }
        else{
            $customerName = getCustomerByCode($customerCode)["customerName"];
        }

        create_payment($no_sj, $payment_date, $payment_amount);

        if (isset($_POST['generate_pdf'])){
            if($pageState == "in"){
                create_payment_in_pdf($storageName, $vendorName, $no_sj, $no_truk, $purchase_order, $invoice_date, $no_LPB, $productCodes, $productNames, $qtys, $uoms, $price_per_uom, $payment_amount, $payment_date);
            }
            else{
                create_payment_out_pdf($storageName, $customerName, $no_sj, $customerAddress, $npwp, $invoice_date, $no_invoice, $productCodes, $productNames, $qtys, $uoms, $price_per_uom, $payment_amount, $payment_date);
            }
            exit;
        }

        header("Location:../controller/index.php?action=dashboard");
        break;

    case "calculateHutang":
        $no_sj = filter_input(INPUT_GET, "no_sj", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $remaining = 0;

        if($no_sj != null){
            $payment_amount = filter_input(INPUT_GET, "payment_amount", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $totalNominal = getTotalNominalByNoSJ($no_sj)["totalNominal"];
            $totalPayment = getTotalPayment($no_sj)["totalPayment"];
            
            $totalNominal = $totalNominal + ($totalNominal * 0.11);
            if($payment_amount != null){
                $remaining = $totalNominal - $totalPayment - $payment_amount;
                echo $remaining;
            }
            else{
                $remaining = $totalNominal - $totalPayment;
                echo $remaining;
            }
        }
        else{
            echo $remaining;
        }
        break;

    case "create_repack":
        $storageCode = filter_input(INPUT_POST, "storageCode", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $repack_date = filter_input(INPUT_POST, "repack_date", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $no_repack = filter_input(INPUT_POST, "no_repack", FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $kd_awal = filter_input_array(INPUT_POST)["kd_awal"];
        $qty_awal = filter_input_array(INPUT_POST)["qty_awal"];
        $uom_awal = filter_input_array(INPUT_POST)["uom_awal"];
        $note_awal = filter_input_array(INPUT_POST)["note_awal"];

        $kd_akhir = filter_input_array(INPUT_POST)["kd_akhir"];
        $qty_akhir = filter_input_array(INPUT_POST)["qty_akhir"];
        $uom_akhir = filter_input_array(INPUT_POST)["uom_akhir"];
        $note_akhir = filter_input_array(INPUT_POST)["note_akhir"];

        create_repack($storageCode, $repack_date, $no_repack);

        for($i = 0; $i < count($kd_awal); $i++){
            addOrderProducts($no_repack, $kd_awal[$i], $qty_awal[$i], $uom_awal[$i], $note_awal[$i], "repack_awal");
        }
        for($i = 0; $i < count($kd_akhir); $i++){
            addOrderProducts($no_repack, $kd_akhir[$i], $qty_akhir[$i], $uom_akhir[$i], $note_akhir[$i], "repack_akhir");
        }

        header("Location:../controller/index.php?action=dashboard");
        break;

    case "create_moving":
        $storageCodeSender = filter_input(INPUT_POST, "storageCodeSender", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $storageCodeReceiver = filter_input(INPUT_POST, "storageCodeReceiver", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $no_moving = filter_input(INPUT_POST, "no_moving", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $moving_date = filter_input(INPUT_POST, "moving_date", FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $productCodes = filter_input_array(INPUT_POST)["kd"];
        $qtys = filter_input_array(INPUT_POST)["qty"];
        $uoms = filter_input_array(INPUT_POST)["uom"];
        $price_per_uom = filter_input_array(INPUT_POST)["price_per_uom"];

        create_moving($no_moving, $moving_date, $storageCodeSender, $storageCodeReceiver);

        for($i = 0; $i < count($productCodes); $i++){
            addOrderProducts($no_moving, $productCodes[$i], $qtys[$i], $uoms[$i], "", "moving");
        }

        for($i = 0; $i < count($productCodes); $i++){
            updatePriceForProductsMoving($no_moving, $productCodes[$i], $price_per_uom[$i]);
        }

        header("Location:../controller/index.php?action=dashboard");
        break;

    case "test":
        $groupData = [];
        $hutangDetails = getHutangDetails(7, 2024, "APA", "hutang");
        foreach($hutangDetails as $details){
            $hutangKey = $details["nomor_surat_jalan"];
            if(!isset($groupData[$hutangKey])){
                $groupData[$hutangKey] = [
                    "invoice_date" => $details["invoice_date"],
                    "no_invoice" => $details["no_invoice"],
                    "vendorName" => $details["vendorName"],
                    "payments" => [],
                    "products" => []
                ];

                $productsList = getProductsForHutang($hutangKey);
                foreach($productsList as $key){
                    array_push($groupData[$hutangKey]["products"], [
                        "productCode" => $key["productCode"],
                        "qty" => $key["qty"],
                        "price_per_UOM" => $key["price_per_UOM"],
                        "nominal" => $key["nominal"]
                    ]);
                }
            }

            array_push($groupData[$hutangKey]["payments"], [
                "payment_date" => $details["payment_date"],
                "payment_amount" => $details["payment_amount"]
            ]);
        }
        echo "<br>";
        echo "<pre>" . print_r(array_values($groupData), true) . "</pre>";
        // $products = getProductsForHutang("001/SJJ/SOME/12/2024");
        // var_dump($products);
        break;

    case "getLaporanHutang":
        $month = filter_input(INPUT_GET, "month");
        $year = filter_input(INPUT_GET, "year");
        $storageCode = filter_input(INPUT_GET, "storageCode");
        echo json_encode(getLaporanHutangPiutang($month, $year, $storageCode, "hutang"));
        break;

    case "getLaporanPiutang":
        $month = filter_input(INPUT_GET, "month");
        $year = filter_input(INPUT_GET, "year");
        echo json_encode(getLaporanHutangPiutang($month, $year, "NON", "piutang"));
        break;
}


?>