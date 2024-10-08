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
require_once "../model/saldo_functions.php";
require_once "../model/utility_functions.php";
require_once "../model/order_products_functions.php";
require_once "../fpdf/fpdf.php";
require_once "../model/pdf_creation.php";
require_once "../model/excel_creation.php";

global $db;

$action = filter_input(INPUT_GET, "action");
$title = "";
$pageState = "";
$msg = filter_input(INPUT_GET, "msg", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
if($action == null){
    $action = "show_login";
}

if($msg == null){
    $msg = "";
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

if (!checkAccess($action, $userType)) {
    header("Location:../controller/index.php?action=dashboard");
    exit;
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

    case "show_slip":
        if($userID == null){
            header("Location:../controller/index.php?action=show_login");
        }
        $pageState = filter_input(INPUT_GET, "state", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $title = "slip " . $pageState;
        require_once "../view/slip.php";
        break;

    case "show_invoice":
        if($userID == null){
            header("Location:../controller/index.php?action=show_login");
        }
        $pageState = filter_input(INPUT_GET, "state", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $title = "invoice " . $pageState;
        require_once "../view/invoice.php";
        break;

    case "show_payment":
        if($userID == null){
            header("Location:../controller/index.php?action=show_login");
        }
        $pageState = filter_input(INPUT_GET, "state", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $title = "payment " . $pageState;
        require_once "../view/payment.php";
        break;

    case "show_repack":
        if($userID == null){
            header("Location:../controller/index.php?action=show_login");
        }
        $title = "repack";
        $pageState = "repack";
        require_once "../view/repack.php";
        break;

    case "show_moving":
        if($userID == null){
            header("Location:../controller/index.php?action=show_login");
        }
        $title = "moving";
        $pageState = "moving";
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

    case "show_amends":
        if($userID == null){
            header("Location:../controller/index.php?action=show_login");
        }
        $title = "amends";
        $state = filter_input(INPUT_GET, "state", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        switch($state){
            case "slip":
                $no_SJs = getAllOrders();
                break;
            case "invoice":
                $no_SJs = getAllInvoices();
                break;
            case "payment":
                $no_SJs = getAllPayments();
                break;
            case "repack":
                $no_SJs = getAllRepacks();
                break;
            case "moving":
                $no_SJs = getAllMovings();
                break;
        }
        require_once "../view/amends.php";
        break;

    case "master_read":
        $title = "master read";
        $data = filter_input(INPUT_GET, "data", FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        switch($data){
            case "vendor":
                $result = getAllVendors();
                $keyNames = getAllVendorsKeyNames();
                break;
            case "product":
                $result = getAllProducts();
                $keyNames = getAllProductsKeyNames();
                break;
            case "customer":
                $result = getAllCustomers();
                $keyNames = getAllCustomersKeyNames();
                break;
            case "storage":
                $result = getAllStorages();
                $keyNames = getAllStoragesKeyNames();
                break;
            case "users":
                $result = getAllUsers();
                $keyNames = getAllUsersKeyNames();
                break;
        }

        require_once "../view/read.php";
        //echo "<pre>" . print_r(getAllStoragesKeyNames(), true) . "</pre>";
        break;

    case "master_create":
        $title = "master create";
        $data = filter_input(INPUT_GET, "data", FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        switch($data){
            case "vendor":
                $keyNames = getAllVendorsKeyNames();
                break;
            case "product":
                $keyNames = getAllProductsKeyNames();
                break;
            case "customer":
                $keyNames = getAllCustomersKeyNames();
                break;
            case "storage":
                $keyNames = getAllStoragesKeyNames();
                break;
            case "users":
                $keyNames = getAllUsersKeyNames();
                require_once "../view/register.php";
                exit;
                break;
        }

        require_once "../view/create.php";
        break;

    case "master_create_data":
        $data = filter_input(INPUT_POST, "data", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $input_data = filter_input_array(INPUT_POST)["input_data"];

        switch($data){
            case "vendor":
                $flag = createVendor($input_data[0], $input_data[1], $input_data[2], $input_data[3]);
                break;
            case "product":
                $flag = createProduct($input_data[0], $input_data[1]);
                break;
            case "customer":
                $flag = createCustomer($input_data[0], $input_data[1], $input_data[2], $input_data[3]);
                break;
            case "storage":
                if(isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
                    $logo_tmp_name = $_FILES['logo']['tmp_name'];
                    $storageCode = $input_data[0]; // Assuming input_data[0] is the storageCode
                    $logo_extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
                    $logo_new_name = $storageCode . '.' . $logo_extension;
                    $logo_destination = "../img/" . $logo_new_name;
            
                    if(move_uploaded_file($logo_tmp_name, $logo_destination)) {
                        $flag = createStorage($input_data[0], $input_data[1], $input_data[2], $input_data[3]);
                    } else {
                        $flag = false;
                    }
                } else {
                    $flag = false;
                }
                break;
            case "users":
                $flag = register($input_data[0], $input_data[1], $input_data[2]);
                break;
                
        }

        if(!$flag){
            header("Location:../controller/index.php?action=master_create&data=" . $data . "&msg=code existed already");
        }
        else{
            header("Location:../controller/index.php?action=master_read&data=" . $data . "&msg=created data");
        }
        break;

    case "master_update":
        $title = "master update";
        $data = filter_input(INPUT_GET, "data", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $code = filter_input(INPUT_GET, "code", FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        switch($data){
            case "vendor":
                $keyNames = getAllVendorsKeyNames();
                $result = getVendorByCode($code);
                break;
            case "product":
                $keyNames = getAllProductsKeyNames();
                $result = getProductByCode($code);
                break;
            case "customer":
                $keyNames = getAllCustomersKeyNames();
                $result = getCustomerByCode($code);
                break;
            case "storage":
                $keyNames = getAllStoragesKeyNames();
                $result = getstorageByCode($code);
                break;
            case "users":
                $result = getUserByCode($code);
                require_once "../view/register.php";
                exit;
                break;
        }

        require_once "../view/update.php";
        break;

    case "master_update_data":
        $data = filter_input(INPUT_POST, "data", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $oldCode = filter_input(INPUT_POST, "oldCode", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $input_data = filter_input_array(INPUT_POST)["input_data"];

        switch($data){
            case "vendor":
                $flag = updateVendor($input_data[0], $input_data[1], $input_data[2], $input_data[3], $oldCode);
                break;
            case "product":
                $flag = updateProduct($input_data[0], $input_data[1], $oldCode);
                break;
            case "customer":
                $flag = updateCustomer($input_data[0], $input_data[1], $input_data[2], $input_data[3], $oldCode);
                break;
            case "storage":
                if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
                    if (!empty($oldCode)) {
                        $old_logo_path = "../img/" . $oldCode . '.' . pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            
                        if (file_exists($old_logo_path)) {
                            unlink($old_logo_path); // Delete the old logo
                        }
                    }
            
                    $logo_tmp_name = $_FILES['logo']['tmp_name'];
                    $storageCode = $input_data[0]; // New storage code
                    $logo_extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
                    $logo_new_name = $storageCode . '.' . $logo_extension;
                    $logo_destination = "../img/" . $logo_new_name;
            
                    if (move_uploaded_file($logo_tmp_name, $logo_destination)) {
                        $flag = updateStorage($input_data[0], $input_data[1], $input_data[2], $input_data[3], $oldCode);
                    } else {
                        $flag = false;
                    }
                } else {
                    $flag = false;
                }
                break;
            case "users":
                $flag = updateUser($input_data[0], $input_data[1], $input_data[2], $oldCode);
                break;
                
        }

        if($flag){
            header("Location:../controller/index.php?action=master_read&data=" . $data . "&msg=updated data");
        }
        else if($flag == "duplicate"){
            header("Location:../controller/index.php?action=master_update&data=" . $data . "&msg=code existed already");
        }
        else if($flag == "foreign"){
            header("Location:../controller/index.php?action=master_update&data=" . $data . "&msg=code is messing with the orders on the order products");
        }
        else{
            header("Location:../controller/index.php?action=master_update&data=" . $data . "&msg=code update error");
        }
        break;

    case "master_delete":
        $title = "master delete";
        $data = filter_input(INPUT_GET, "data", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $code = filter_input(INPUT_GET, "code", FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        require_once "../view/delete.php";
        break;

    case "master_delete_data":
        $data = filter_input(INPUT_POST, "data", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $code = filter_input(INPUT_POST, "code", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        switch($data){
            case "vendor":
                $flag = deleteVendor($code);
                break;
            case "product":
                $flag = deleteProduct($code);
                break;
            case "customer":
                $flag = deleteCustomer($code);
                break;
            case "storage":
                $flag = deleteStorage($code);
                break;
            case "users":
                $flag = deleteUser($code);
                break;
        }

        if($flag){
            header("Location:../controller/index.php?action=master_read&data=" . $data . "&msg=deleted data");
        }
        else if($flag == "foreign"){
            header("Location:../controller/index.php?action=master_read&data=" . $data . "&msg=code is messing with the orders or linked to something else");
        }
        else{
            header("Location:../controller/index.php?action=master_read&data=" . $data . "&msg=code delete error");
        }

        require_once "../view/delete.php";
        break;

    case "amend_update":
        $data = filter_input(INPUT_GET, "data", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $code = filter_input(INPUT_GET, "code", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $payment_id = filter_input(INPUT_GET, "payment_id", FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (!checkAccess($data, $userType)) {
            header("Location:../controller/index.php?action=dashboard");
            exit;
        }

        switch($data){
            case "slip":
                $title = "amend slip";
                $result = getOrderByNoSJ($code);
                if($result["status_mode"] == 1){
                    $pageState = "amend_slip_in";
                }
                else if($result["status_mode"] == 2){
                    $pageState = "amend_slip_out";
                }
                else{
                    $pageState = "amend_slip_out_tax";
                }
                $products = getOrderProductsFromNoID($code, "in");
                require_once "../view/amend_slip.php";
                break;
            case "invoice":
                $title = "amend invoice";
                if(!strpos($code, "SJP")){
                    $result = getOrderByNoSJ($code);
                    $invoice = getInvoiceByNoSJ($code, null);
    
                    if($result["status_mode"] == 1){
                        $pageState = "amend_invoice_in";
                    }
                    else if($result["status_mode"] == 2){
                        $pageState = "amend_invoice_out";
                    }
                    else{
                        $pageState = "amend_invoice_out_tax";
                    }
                    $products = getOrderProductsFromNoID($code, "in");
                }
                else{
                    $result = getMovingByCode($code);
                    $invoice = getInvoiceByNoSJ(null, $code);
                    $pageState = "amend_invoice_moving";
    
                    $products = getOrderProductsFromNoID($code, "moving");
                }
                require_once "../view/amend_invoice.php";
                break;
            case "payment":
                $title = "amend payment";
                if(!strpos($code, "SJP")){
                    $result = getOrderByNoSJ($code);
                    $invoice = getInvoiceByNoSJ($code, null);
                    $payment = getPaymentByID($payment_id);
    
                    if($result["status_mode"] == 1){
                        $pageState = "amend_payment_in";
                    }
                    else if($result["status_mode"] == 2){
                        $pageState = "amend_payment_out";
                    }
                    else{
                        $pageState = "amend_payment_out_tax";
                    }
                    $products = getOrderProductsFromNoID($code, "in");
                }
                else{
                    $result = getMovingByCode($code);
                    $invoice = getInvoiceByNoSJ(null, $code);
                    $payment = getPaymentByID($payment_id);
                    $pageState = "amend_payment_moving";

                    $products = getOrderProductsFromNoID($code, "moving");
                }
                require_once "../view/amend_payment.php";
                break;
            case "repack":
                $title = "amend repack";
                $pageState = "amend_repack";
                $result = getRepackByCode($code);
                $products = getOrderProductsFromNoID($code, "repack");
                require_once "../view/amend_repack.php";
                break;
            case "moving":
                $title = "amend moving";
                $pageState = "amend_moving";
                $result = getMovingByCode($code);
                $products = getOrderProductsFromNoID($code, "moving");
                require_once "../view/amend_moving.php";
                break;
        }
        break;

    case "amend_update_data":
        $data = filter_input(INPUT_GET, "data", FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $storageCode = filter_input(INPUT_POST, "storageCode", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $no_LPB = filter_input(INPUT_POST, "no_LPB", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $no_sj = filter_input(INPUT_POST, "no_sj", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $old_sj = filter_input(INPUT_POST, "old_sj", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $no_truk = filter_input(INPUT_POST, "no_truk", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $vendorCode = filter_input(INPUT_POST, "vendorCode", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $customerCode = filter_input(INPUT_POST, "customerCode", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $customerAddress = filter_input(INPUT_POST, "customerAddress", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $npwp = filter_input(INPUT_POST, "npwp", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $order_date = filter_input(INPUT_POST, "order_date", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $purchase_order = filter_input(INPUT_POST, "purchase_order", FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $pageState = filter_input(INPUT_POST, "pageState", FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (!checkAccess($data, $userType)) {
            header("Location:../controller/index.php?action=dashboard");
            exit;
        }

        switch($data){
            case "slip":
                $productCodes = filter_input_array(INPUT_POST)["kd"];
                $productNames = filter_input_array(INPUT_POST)["material"];
                $qtys = filter_input_array(INPUT_POST)["qty"];
                $uoms = filter_input_array(INPUT_POST)["uom"];
                $notes = filter_input_array(INPUT_POST)["note"];
                // Retrieve current order products and their prices
                $currentOrderProducts = getOrderProductsFromNoID($no_sj, "in");
            
                // Create an associative array to store product prices
                $productPrices = [];
                foreach ($currentOrderProducts as $product) {
                    $productPrices[$product['productCode']] = $product['price_per_UOM'];
                }
            
                // Delete existing order products
                deleteOrderProducts($no_sj, "order");
            
                if ($pageState == "amend_slip_in") {
                    $result = updateOrderWithDependencies($no_sj, $storageCode, $no_LPB, $no_truk, $vendorCode, "NON", $order_date, $purchase_order, $old_sj);
                    if ($result === true) {
                        for ($i = 0; $i < count($productCodes); $i++) {
                            $price = isset($productPrices[$productCodes[$i]]) ? $productPrices[$productCodes[$i]] : 0;
                            addOrderProducts($no_sj, $productCodes[$i], $qtys[$i], $uoms[$i], $price, $notes[$i], "in");
                        }
                    }
                } else if ($pageState == "amend_slip_out") {
                    $result = updateOrderWithDependencies($no_sj, $storageCode, $no_LPB, $no_truk, "NON", $customerCode, $order_date, $purchase_order, $old_sj);
                    if ($result === true) {
                        for ($i = 0; $i < count($productCodes); $i++) {
                            $price = isset($productPrices[$productCodes[$i]]) ? $productPrices[$productCodes[$i]] : 0;
                            addOrderProducts($no_sj, $productCodes[$i], $qtys[$i], $uoms[$i], $price, $notes[$i], "out");
                        }
                    }
                } else {
                    $result = updateOrderWithDependencies($no_sj, $storageCode, $no_LPB, $no_truk, "NON", $customerCode, $order_date, $purchase_order, $old_sj);
                    if ($result === true) {
                        for ($i = 0; $i < count($productCodes); $i++) {
                            $price = isset($productPrices[$productCodes[$i]]) ? $productPrices[$productCodes[$i]] : 0;
                            addOrderProducts($no_sj, $productCodes[$i], $qtys[$i], $uoms[$i], $price, $notes[$i], "out_tax");
                        }
                    }
                }
                break;
            
            
            case "invoice":
                $productCodes = filter_input_array(INPUT_POST)["kd"];
                $productNames = filter_input_array(INPUT_POST)["material"];
                $qtys = filter_input_array(INPUT_POST)["qty"];
                $uoms = filter_input_array(INPUT_POST)["uom"];
                $notes = filter_input_array(INPUT_POST)["note"];
                
                $invoice_date = filter_input(INPUT_POST, "invoice_date", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $no_invoice = filter_input(INPUT_POST, "no_invoice", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $no_faktur = filter_input(INPUT_POST, "no_faktur", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $price_per_uom = filter_input_array(INPUT_POST)["price_per_uom"];
                $tax = filter_input(INPUT_POST, "tax", FILTER_SANITIZE_FULL_SPECIAL_CHARS);

                if($pageState == "amend_invoice_moving"){
                    $no_moving = filter_input(INPUT_POST, "no_moving", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                    updateInvoice("-", $invoice_date, $no_invoice, $no_faktur, $no_moving, $tax);
                    for($i = 0; $i < count($productCodes); $i++){
                        updatePriceForProductsMoving($no_moving, $productCodes[$i], $price_per_uom[$i]);
                    }
                }
                else{
                    updateInvoice($no_sj, $invoice_date, $no_invoice, $no_faktur, "-", $tax);
                    for($i = 0; $i < count($productCodes); $i++){
                        updatePriceForProducts($no_sj, $productCodes[$i], $price_per_uom[$i]);
                    }
                }
                break;
            case "payment":
                $payment_id = filter_input(INPUT_POST, "payment_id", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $payment_date = filter_input(INPUT_POST, "payment_date", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $payment_amount = filter_input(INPUT_POST, "payment_amount", FILTER_SANITIZE_FULL_SPECIAL_CHARS);

                if($pageState == "amend_payment_moving"){
                    $no_moving = filter_input(INPUT_POST, "no_moving", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                    updatePayment("-", $payment_date, $payment_amount, $no_moving, $payment_id);
                }
                else{
                    updatePayment($no_sj, $payment_date, $payment_amount, "-", $payment_id);
                }
                break;
            case "repack":
                $repack_date = filter_input(INPUT_POST, "repack_date", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $no_repack = filter_input(INPUT_POST, "no_repack", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $old_repack = filter_input(INPUT_POST, "old_repack", FILTER_SANITIZE_FULL_SPECIAL_CHARS);

                $kd_awal = filter_input_array(INPUT_POST)["kd_awal"];
                $qty_awal = filter_input_array(INPUT_POST)["qty_awal"];
                $uom_awal = filter_input_array(INPUT_POST)["uom_awal"];
                $note_awal = filter_input_array(INPUT_POST)["note_awal"];

                $kd_akhir = filter_input_array(INPUT_POST)["kd_akhir"];
                $qty_akhir = filter_input_array(INPUT_POST)["qty_akhir"];
                $uom_akhir = filter_input_array(INPUT_POST)["uom_akhir"];
                $note_akhir = filter_input_array(INPUT_POST)["note_akhir"];

                deleteOrderProducts($old_repack, "repack");
                updateRepack($no_repack, $repack_date, $storageCode, $old_repack);
                for($i = 0; $i < count($kd_awal); $i++){
                    addOrderProducts($no_repack, $kd_awal[$i], $qty_awal[$i], $uom_awal[$i], 0, $note_awal[$i], "repack_awal");
                }
                for($i = 0; $i < count($kd_akhir); $i++){
                    addOrderProducts($no_repack, $kd_akhir[$i], $qty_akhir[$i], $uom_akhir[$i], 0, $note_akhir[$i], "repack_akhir");
                }
                break;
            case "moving":
                $productCodes = filter_input_array(INPUT_POST)["kd"];
                $productNames = filter_input_array(INPUT_POST)["material"];
                $qtys = filter_input_array(INPUT_POST)["qty"];
                $uoms = filter_input_array(INPUT_POST)["uom"];

                $storageCodeSender = filter_input(INPUT_POST, "storageCodeSender", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $storageCodeReceiver = filter_input(INPUT_POST, "storageCodeReceiver", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $no_moving = filter_input(INPUT_POST, "no_moving", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $old_moving = filter_input(INPUT_POST, "old_moving", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $moving_date = filter_input(INPUT_POST, "moving_date", FILTER_SANITIZE_FULL_SPECIAL_CHARS);

                deleteOrderProducts($old_moving, "moving");
                updateMoving($no_moving, $moving_date, $storageCodeSender, $storageCodeReceiver, $old_moving);
                for($i = 0; $i < count($productCodes); $i++){
                    addOrderProducts($no_moving, $productCodes[$i], $qtys[$i], $uoms[$i], 0, "", "moving");
                }
                break;
        }
        header("Location:../controller/index.php?action=show_amends&state=" . $data);
        break;
    
    case "amend_delete_data":
        $data = filter_input(INPUT_POST, "data", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $code = filter_input(INPUT_POST, "code", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    
        if (!checkAccess($data, $userType)) {
            header("Location:../controller/index.php?action=dashboard");
            exit;
        }
    
        $db->beginTransaction();
    
        try {
            $success = false;
    
            switch ($data) {
                case "slip":
                    $success = deleteOrderProducts($code, "order");
    
                    if ($success === true) {
                        $success = deleteMultiPayment($code);
                    }
    
                    if ($success === true) {
                        $success = deleteInvoice($code);
                    }
    
                    if ($success === true) {
                        $success = deleteOrder($code);
                    }
                    break;
    
                case "invoice":
                    $success = deleteMultiPayment($code);
    
                    if ($success === true) {
                        if(strpos($code, "SJP")){
                            $result = getOrderProductsFromNoID($code, "moving");
                            foreach ($result as $key) {
                                $success = updatePriceForProductsMoving($code, $key["productCode"], 0);
                                if ($success !== true) break; // Exit the loop if any update fails
                            }
                        }
                        else{
                            $result = getOrderProductsFromNoID($code, "in");
                            foreach ($result as $key) {
                                $success = updatePriceForProducts($code, $key["productCode"], 0);
                                if ($success !== true) break; // Exit the loop if any update fails
                            }
                        }
                    }
    
                    if ($success === true) {
                        $success = deleteInvoice($code);
                    }
                    break;
    
                case "payment":
                    $success = deleteMultiPayment($code);
                    break;
    
                case "repack":
                    $success = deleteOrderProducts($code, "repack");
                    if($success === true){
                        $success = deleteRepack($code);
                    }
                    break;
    
                case "moving":
                    $success = deleteOrderProducts($code, "moving");
                    if($success === true){
                        $success = deleteMoving($code);
                    }
                    break;
            }
    
            if ($success === true) {
                $db->commit();
                header("Location:../controller/index.php?action=show_amends&state=" . $data . "&msg=record deleted");
            } elseif ($success === 'foreign_key') {
                throw new Exception("Foreign key constraint violation. Deletion not allowed.");
            } else {
                throw new Exception("An error occurred while deleting the record.");
            }
        } catch (Exception $e) {
            $db->rollBack();
            header("Location:../controller/index.php?action=show_amends&state=" . $data . "&msg=" . $e->getMessage());
        }
        break;
        
        

    case "generate_LPB":
        $storageCode = filter_input(INPUT_GET, "storageCode", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $month = filter_input(INPUT_GET, "month", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $year = filter_input(INPUT_GET, "year", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        echo generateNoLPB($storageCode, $month, $year, "1");
        break;

    case "generate_SJ":
        $storageCode = filter_input(INPUT_GET, "storageCode", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $month = filter_input(INPUT_GET, "month", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $year = filter_input(INPUT_GET, "year", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        echo generateNoLPB($storageCode, $month, $year, "2");
        break;

    case "generate_SJT":
        $storageCode = filter_input(INPUT_GET, "storageCode", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $month = filter_input(INPUT_GET, "month", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $year = filter_input(INPUT_GET, "year", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        echo generateTaxSJ($storageCode, $month, $year);
        break;

    case "generate_SJR":
        $storageCode = filter_input(INPUT_GET, "storageCode", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $month = filter_input(INPUT_GET, "month", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $year = filter_input(INPUT_GET, "year", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        echo generate_SJR($storageCode, $month, $year);
        break;

    case "generate_SJP":
        $storageCode = filter_input(INPUT_GET, "storageCode", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $month = filter_input(INPUT_GET, "month", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $year = filter_input(INPUT_GET, "year", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        echo generate_SJP($storageCode, $month, $year);
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

    case "getMovingDetails":
        $no_moving = filter_input(INPUT_GET, "no_moving", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        echo json_encode(getMovingByCode($no_moving));
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
        echo json_encode(getInvoiceByNoSJ($no_sj, null));
        break;

    case "getInvoiceMovingByNoSJ":
        $no_sj = filter_input(INPUT_GET, 'no_sj', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        echo json_encode(getInvoiceByNoSJ(null, $no_sj));
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
            $success = create_slip($no_sj, $storageCode, $no_LPB, $no_truk, $vendorCode, "NON", $order_date, $purchase_order, 1);
            if($success){
                for($i = 0; $i < count($productCodes); $i++){
                    addOrderProducts($no_sj, $productCodes[$i], $qtys[$i], $uoms[$i], 0, $notes[$i], "in");
                }
                //create_NON_slip($no_truk, $vendorCode, $order_date, $purchase_order);
            }
        }
        else if($pageState == "out"){
            $success = create_slip($no_sj, $storageCode, $no_LPB, $no_truk, "NON", $customerCode, $order_date, $purchase_order, 2);
            if($success){
                for($i = 0; $i < count($productCodes); $i++){
                    addOrderProducts($no_sj, $productCodes[$i], $qtys[$i], $uoms[$i], 0, $notes[$i], "out");
                }
            }
        }
        else{
            $success = create_slip($no_sj, $storageCode, $no_LPB, $no_truk, "NON", $customerCode, $order_date, $purchase_order, 3);
            if($success){
                for($i = 0; $i < count($productCodes); $i++){
                    addOrderProducts($no_sj, $productCodes[$i], $qtys[$i], $uoms[$i], 0, $notes[$i], "out_tax");
                }
            }
        }

        if($success){
            header("Location:../controller/index.php?action=show_invoice&msg=NO_sj:" . $no_sj . "&state=" . $pageState);
        }
        else{
            header("Location:../controller/index.php?action=show_slip&msg=" . $no_sj . " is already in the database&state=" . $pageState);
        }

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
        $tax = filter_input(INPUT_POST, "tax", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $productCodes = filter_input_array(INPUT_POST)["kd"];
        $productNames = filter_input_array(INPUT_POST)["material"];
        $qtys = filter_input_array(INPUT_POST)["qty"];
        $uoms = filter_input_array(INPUT_POST)["uom"];
        $price_per_uom = filter_input_array(INPUT_POST)["price_per_uom"];
        $pageState = filter_input(INPUT_POST, "pageState", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $vendorName = "";
        $customerName = "";

        if($pageState == "moving"){
            $storageCodeSender = filter_input(INPUT_POST, "storageCodeSender", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $storageCodeReceiver = filter_input(INPUT_POST, "storageCodeReceiver", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $no_moving = filter_input(INPUT_POST, "no_moving", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $moving_date = filter_input(INPUT_POST, "moving_date", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            if(invoiceMovingExists($no_moving)){
                header("Location:../controller/index.php?action=show_invoice&state=" . $pageState . "&msg=there is already an invoice with that no_moving");
                exit;
            }
            else{
                create_invoice("-", $invoice_date, $no_invoice, $no_faktur, $no_moving, $tax);
            }

            $storageNameSender = getstorageByCode($storageCodeSender)["storageName"];
            $storageNameReceiver = getstorageByCode($storageCodeReceiver)["storageName"];
        }
        else{
            $storageName = getstorageByCode($storageCode)["storageName"];
            if(invoiceSJExists($no_sj)){
                header("Location:../controller/index.php?action=show_invoice&state=" . $pageState . "&msg=there is already an invoice with that no_sj");
                exit;
            }
            else{
                create_invoice($no_sj, $invoice_date, $no_invoice, $no_faktur, "-", $tax);
                if($pageState == "in"){
                    $vendorName = getVendorByCode($vendorCode)["vendorName"];
                    //create_NON_invoice($no_LPB, $invoice_date, $no_invoice, $no_faktur, $tax);
                }
                else{
                    $customerName = getCustomerByCode($customerCode)["customerName"];
                }
            }
        }
        // $date = DateTime::createFromFormat('Y-m-d', $invoice_date);
        // $month = $date->format('m');
        // $year = $date->format('Y');

        if($pageState != "moving"){
            for($i = 0; $i < count($productCodes); $i++){
                updatePriceForProducts($no_sj, $productCodes[$i], $price_per_uom[$i]);
            }
        }
        else{
            for($i = 0; $i < count($productCodes); $i++){
                updatePriceForProductsMoving($no_moving, $productCodes[$i], $price_per_uom[$i]);
            }
        }

        // Generate the PDF and return it as a response
        if (isset($_POST['generate_pdf'])) {
            if($pageState == "in"){
                create_invoice_in_pdf($storageCode, $storageName, $vendorName, $no_sj, $no_truk, $purchase_order, $invoice_date, $no_LPB, $no_invoice, $productCodes, $productNames, $qtys, $uoms, $price_per_uom, $no_faktur, $tax);
            }
            else if($pageState == "out" || $pageState == "out_tax"){
                create_invoice_out_pdf($storageCode, $storageName, $customerName, $no_sj, $customerAddress, $npwp, $invoice_date, $no_invoice, $productCodes, $productNames, $qtys, $uoms, $price_per_uom, $no_faktur, $tax);
            }
            else{
                create_invoice_moving_pdf($storageNameSender, $storageNameReceiver, $no_moving, $moving_date, $invoice_date, $no_invoice, $productCodes, $productNames, $qtys, $uoms, $price_per_uom, $no_faktur, $tax);
            }
            exit;
        }

        // Redirect to the dashboard
        header("Location:../controller/index.php?action=show_payment&msg=NO_sj:" . $no_sj . "&state=" . $pageState);
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
        $tax = filter_input(INPUT_POST, "tax", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $payment_date = filter_input(INPUT_POST, "payment_date", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $payment_amount = filter_input(INPUT_POST, "payment_amount", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $productCodes = filter_input_array(INPUT_POST)["kd"];
        $productNames = filter_input_array(INPUT_POST)["material"];
        $qtys = filter_input_array(INPUT_POST)["qty"];
        $uoms = filter_input_array(INPUT_POST)["uom"];
        $price_per_uom = filter_input_array(INPUT_POST)["price_per_uom"];
        $pageState = filter_input(INPUT_POST, "pageState", FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if($pageState == "moving"){
            $storageCodeSender = filter_input(INPUT_POST, "storageCodeSender", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $storageCodeReceiver = filter_input(INPUT_POST, "storageCodeReceiver", FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $storageNameSender = getstorageByCode($storageCodeSender)["storageName"];
            $storageNameReceiver = getstorageByCode($storageCodeReceiver)["storageName"];

            $no_moving = filter_input(INPUT_POST, "no_moving", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $moving_date = filter_input(INPUT_POST, "moving_date", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            create_payment("-", $payment_date, $payment_amount, $no_moving);
        }
        else{
            $storageName = getstorageByCode($storageCode)["storageName"];
            if($pageState == "in"){
                $vendorName = getVendorByCode($vendorCode)["vendorName"];
                //create_NON_payment($no_LPB, $payment_date, $payment_amount);
            }
            else{
                $customerName = getCustomerByCode($customerCode)["customerName"];
            }
            create_payment($no_sj, $payment_date, $payment_amount, "-");
        }


        if (isset($_POST['generate_pdf'])){
            if($pageState == "in"){
                create_payment_in_pdf($storageCode, $storageName, $vendorName, $no_sj, $no_truk, $purchase_order, $invoice_date, $no_LPB, $no_invoice, $productCodes, $productNames, $qtys, $uoms, $price_per_uom, $payment_amount, $payment_date, $tax);
            }
            else if($pageState == "out" || $pageState == "out_tax"){
                create_payment_out_pdf($storageCode, $storageName, $customerName, $no_sj, $customerAddress, $npwp, $invoice_date, $no_invoice, $productCodes, $productNames, $qtys, $uoms, $price_per_uom, $payment_amount, $payment_date, $tax);
            }
            else{
                create_payment_moving_pdf($storageNameSender, $storageNameReceiver, $no_moving, $moving_date, $invoice_date, $no_invoice, $productCodes, $productNames, $qtys, $uoms, $price_per_uom, $payment_amount, $payment_date, $tax);
            }
            exit;
        }

        header("Location:../controller/index.php?action=dashboard&msg=payment_made" . "&state=" . $pageState);
        break;

    case "calculateHutang":
        $no_sj = filter_input(INPUT_GET, "no_sj", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $remaining = 0;

        if($no_sj != null){
            $payment_amount = filter_input(INPUT_GET, "payment_amount", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $tax = filter_input(INPUT_GET, "tax", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $totalNominal = getTotalNominalByNoSJ($no_sj)["totalNominal"];
            $totalPayment = getTotalPayment($no_sj)["totalPayment"];
            
            $totalNominal = $totalNominal + ($totalNominal * ((double)$tax / 100));
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

        $date = DateTime::createFromFormat('Y-m-d', $repack_date);
        $month = $date->format('m');
        $year = $date->format('Y');

        create_repack($storageCode, $repack_date, $no_repack);

        for($i = 0; $i < count($kd_awal); $i++){
            addOrderProducts($no_repack, $kd_awal[$i], $qty_awal[$i], $uom_awal[$i], 0, $note_awal[$i], "repack_awal");
        }
        for($i = 0; $i < count($kd_akhir); $i++){
            addOrderProducts($no_repack, $kd_akhir[$i], $qty_akhir[$i], $uom_akhir[$i], 0, $note_akhir[$i], "repack_akhir");
        }


        header("Location:../controller/index.php?action=dashboard&msg=NO_repack:" . $no_repack);
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

        $date = DateTime::createFromFormat('Y-m-d', $moving_date);
        $month = $date->format('m');
        $year = $date->format('Y');

        create_moving($no_moving, $moving_date, $storageCodeSender, $storageCodeReceiver);

        for($i = 0; $i < count($productCodes); $i++){
            addOrderProducts($no_moving, $productCodes[$i], $qtys[$i], $uoms[$i], 0, "", "moving");
        }

        // for($i = 0; $i < count($productCodes); $i++){
        //     updatePriceForProductsMoving($no_moving, $productCodes[$i], $price_per_uom[$i]);
        // }

        header("Location:../controller/index.php?action=dashboard&msg=NO_moving:" . $no_moving);
        break;

    case "test":
        //echo "<pre>" . print_r(json_encode(generateSaldo("APA", 8, 2024)), true) . "</pre>";
        //echo "<pre>" . print_r(getAllProductsForSaldo("APA", 8, 2024), true) . "</pre>";
        //create_invoice_moving_pdf("APA", "BB", "FF", "2022-12-12", "2022-12-12", "op", ["rr-120"], ["regulaer"], [3], ["tray"], [123.12], 11111);
        //create_invoice_in_pdf("APA", "BBB", "ASAS", "TRUK", "123", "222-22-22", "1/LPB?APA?00/231", "no_invoice", ["rr-120"], ["regulaer"], [3], ["tray"], [123.12], 11111);
        //create_invoice_out_pdf("RQQ", "APA", "BBB", "ASAS", "ADDRESS", "NPWP", "222-22-22", "no_invoice", ["rr-120"], ["regulaer"], [3], ["tray"], [123.12], 11111, 11);
        //create_payment_in_pdf("RQQ", "rorqual", "vendor", "sj1", "truk", "po1", "2202-22-22", "LPB", "saINV", ["rr-120"], ["regulaer"], [3], ["tray"], [123.12], 1200, "12-12-121", 11);
        echo "<pre>" . print_r(json_encode(getLaporanHutangPiutang("APA", 8, 2024, "hutang")), true) . "</pre>";
        break;

    case "test2":
        //echo "<pre>" . print_r(json_encode(generateSaldo("NON", 8, 2024)), true) . "</pre>";
        //echo "<pre>" . print_r(json_encode(getAllProductsForSaldo("APA", 8, 2024)), true) . "</pre>";
        //report_stock_excel("APA", "08", "2024");
        break;

    case "getHPP":
        $storageCode = filter_input(INPUT_GET, "storageCode");
        $month = filter_input(INPUT_GET, "month");
        $year = filter_input(INPUT_GET, "year");
        $productCode = filter_input(INPUT_GET, "productCode");
        
        $data = generateSaldo($storageCode, $month, $year);
        if(isset($data[$productCode]["barang_siap_dijual"]["price_per_qty"])){
            echo $data[$productCode]["barang_siap_dijual"]["price_per_qty"];
        }
        else{
            echo 0;
        }
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

    case "getReportStock":
        $month = filter_input(INPUT_GET, "month");
        $year = filter_input(INPUT_GET, "year");
        $storageCode = filter_input(INPUT_GET, "storageCode");
        echo json_encode(generateSaldo($storageCode, $month, $year));
        break;

    case "amendDelete":
        $no_id = filter_input(INPUT_GET, "no_id", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $state = filter_input(INPUT_GET, "state", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        break;

    case "create_pdf":
        $pageState = filter_input(INPUT_GET, "pageState", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $payment_id = filter_input(INPUT_GET, "payment_id", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $vendorName = "";
        $customerName = "";
        $productCodes = [];
        $productNames =[];
        $qtys = [];
        $uoms = [];
        $price_per_uom = [];
        $flag = false;

        if(strpos($pageState, "invoice")){
            $flag = true;
        }

        if($flag){
            if($pageState == "amend_invoice_moving"){
                $no_moving = filter_input(INPUT_GET, "no_moving", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $invoice = getInvoiceByNoSJ(null, $no_moving);
                $order = getMovingByCode($no_moving);
    
                $storageNameSender = getstorageByCode($order["storageCodeSender"])["storageName"];
                $storageNameReceiver = getstorageByCode($order["storageCodeReceiver"])["storageName"];
    
                $products = getOrderProductsFromNoID($no_moving, "moving");
            }
            else{
                $no_sj = filter_input(INPUT_GET, "no_sj", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $invoice = getInvoiceByNoSJ($no_sj, null);
                $order = getOrderByNoSJ($no_sj);
                $storageName = getstorageByCode($order["storageCode"])["storageName"];
                if($pageState == "amend_invoice_in"){
                    $vendorName = getVendorByCode($order["vendorCode"])["vendorName"];
                }
                else{
                    $customerName = getCustomerByCode($order["customerCode"])["customerName"];
                }
                $products = getOrderProductsFromNoID($no_sj, "in");
            }
        }
        else{
            if($pageState == "amend_payment_moving"){
                $no_moving = filter_input(INPUT_GET, "no_moving", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $invoice = getInvoiceByNoSJ(null, $no_moving);
                $order = getMovingByCode($no_moving);
                $payment = getPaymentByID($payment_id);
    
                $storageNameSender = getstorageByCode($order["storageCodeSender"])["storageName"];
                $storageNameReceiver = getstorageByCode($order["storageCodeReceiver"])["storageName"];
    
                $products = getOrderProductsFromNoID($no_moving, "moving");
            }
            else{
                $no_sj = filter_input(INPUT_GET, "no_sj", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $invoice = getInvoiceByNoSJ($no_sj, null);
                $order = getOrderByNoSJ($no_sj);
                $payment = getPaymentByID($payment_id);

                $storageName = getstorageByCode($order["storageCode"])["storageName"];
                if($pageState == "amend_invoice_in"){
                    $vendorName = getVendorByCode($order["vendorCode"])["vendorName"];
                }
                else{
                    $customerName = getCustomerByCode($order["customerCode"])["customerName"];
                }
                $products = getOrderProductsFromNoID($no_sj, "in");
            }
        }

        foreach($products as $key){
            array_push($productCodes, $key["productCode"]);
            array_push($productNames, $key["productName"]);
            array_push($qtys, $key["qty"]);
            array_push($uoms, $key["uom"]);
            array_push($price_per_uom, $key["price_per_UOM"]);
        }

        if($flag){
            if($pageState == "amend_invoice_in"){
                create_invoice_in_pdf($order["storageCode"], $storageName, $vendorName, $no_sj, $order["no_truk"], $order["purchase_order"], $invoice["invoice_date"], $order["no_LPB"], $invoice["no_invoice"], $productCodes, $productNames, $qtys, $uoms, $price_per_uom, $invoice["no_faktur"], $invoice["tax"]);
            }
            else if($pageState == "amend_invoice_out" || $pageState == "amend_invoice_out_tax"){
                create_invoice_out_pdf($order["storageCode"], $storageName, $customerName, $no_sj, $order["customerAddress"], $order["customerNPWP"], $invoice["invoice_date"], $invoice["no_invoice"], $productCodes, $productNames, $qtys, $uoms, $price_per_uom, $invoice["no_faktur"], $invoice["tax"]);
            }
            else{
                create_invoice_moving_pdf($storageNameSender, $storageNameReceiver, $no_moving, $order["moving_date"], $invoice["invoice_date"], $invoice["no_invoice"], $productCodes, $productNames, $qtys, $uoms, $price_per_uom, $invoice["no_faktur"], $invoice["tax"]);
            }
        }
        else{
            if($pageState == "amend_payment_in"){
                create_payment_in_pdf($order["storageCode"], $storageName, $vendorName, $no_sj, $order["no_truk"], $order["purchase_order"], $invoice["invoice_date"], $order["no_LPB"], $invoice["no_invoice"], $productCodes, $productNames, $qtys, $uoms, $price_per_uom, $payment["payment_amount"], $payment["payment_date"], $invoice["tax"]);
            }
            else if($pageState == "amend_payment_out" || $pageState == "amend_payment_out_tax"){
                create_payment_out_pdf($order["storageCode"], $storageName, $customerName, $no_sj, $order["customerAddress"], $order["customerNPWP"], $invoice["invoice_date"], $invoice["no_invoice"], $productCodes, $productNames, $qtys, $uoms, $price_per_uom, $payment["payment_amount"], $payment["payment_date"], $invoice["tax"]);
            }
            else{
                create_payment_moving_pdf($storageNameSender, $storageNameReceiver, $no_moving, $order["moving_date"], $invoice["invoice_date"], $invoice["no_invoice"], $productCodes, $productNames, $qtys, $uoms, $price_per_uom, $payment["payment_amount"], $payment["payment_date"], $invoice["tax"]);
            }
        }

        break;

    case "excel_stock":
        $storageCode = filter_input(INPUT_GET, "storageCode", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $month = filter_input(INPUT_GET, "month", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $year = filter_input(INPUT_GET, "year", FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if($userType == 1){
            report_stock_excel($storageCode, $month, $year);
        }
        else{
            report_stock_excel_normal($storageCode, $month, $year);
        }
        break;

    case "excel_hutang":
        $storageCode = filter_input(INPUT_GET, "storageCode", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $month = filter_input(INPUT_GET, "month", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $year = filter_input(INPUT_GET, "year", FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        excel_hutang_piutang($storageCode, $month, $year, "hutang");
        break;

    case "excel_piutang":
        $month = filter_input(INPUT_GET, "month", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $year = filter_input(INPUT_GET, "year", FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        excel_hutang_piutang("NON", $month, $year, "piutang");
        break;

    case "getLogs":
        getLogs($userType);
        break;
}

?>