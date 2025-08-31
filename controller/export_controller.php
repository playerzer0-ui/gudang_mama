<?php
// controller/export_controller.php
switch($action){
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