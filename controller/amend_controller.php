<?php
// controller/amend_controller.php
switch($action){
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
                
                $currentOrderProducts = getOrderProductsFromNoID($no_sj, "in");
                $productPrices = [];
                foreach ($currentOrderProducts as $product) {
                    $productPrices[$product['productCode']] = $product['price_per_UOM'];
                }
            
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
                                if ($success !== true) break;
                            }
                        }
                        else{
                            $result = getOrderProductsFromNoID($code, "in");
                            foreach ($result as $key) {
                                $success = updatePriceForProducts($code, $key["productCode"], 0);
                                if ($success !== true) break;
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
        
    case "amendDelete":
        $no_id = filter_input(INPUT_GET, "no_id", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $state = filter_input(INPUT_GET, "state", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        break;
}
?>