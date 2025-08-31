<?php
// controller/create_controller.php
switch($action){
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
                }
                else{
                    $customerName = getCustomerByCode($customerCode)["customerName"];
                }
            }
        }

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
            $no_moving = filter_input(INPUT_POST, "no_moving", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $moving_date = filter_input(INPUT_POST, "moving_date", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            create_payment("-", $payment_date, $payment_amount, $no_moving);

            $storageNameSender = getstorageByCode($storageCodeSender)["storageName"];
            $storageNameReceiver = getstorageByCode($storageCodeReceiver)["storageName"];
        }
        else{
            $storageName = getstorageByCode($storageCode)["storageName"];
            if($pageState == "in"){
                $vendorName = getVendorByCode($vendorCode)["vendorName"];
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

        header("Location:../controller/index.php?action=dashboard&msg=NO_moving:" . $no_moving);
        break;
}
?>