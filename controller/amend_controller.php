<?php
// controller/amend_controller.php
require_once "../model/users_action_functions.php";
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

        try {
            $db->beginTransaction();
            // Lock and capture the original record before replacing any products.
            switch ($data) {
                case 'slip':
                    $rows = usersActionFetchRows('SELECT status_mode FROM orders WHERE nomor_surat_jalan = ?', [$old_sj], true);
                    $documentType = [1 => 'slip_in', 2 => 'slip_out', 3 => 'slip_tax'][$rows[0]['status_mode'] ?? 0] ?? null;
                    if ($documentType === null) throw new RuntimeException('Original slip not found.');
                    $beforeReferences = ['no_sj' => $old_sj];
                    $afterReferences = ['no_sj' => $no_sj];
                    $referenceType = $documentType === 'slip_in' ? 'no_LPB' : 'no_sj';
                    break;
                case 'invoice':
                    $documentType = 'invoice';
                    $referenceType = 'no_invoice';
                    $beforeReferences = $pageState === 'amend_invoice_moving'
                        ? ['no_moving' => filter_input(INPUT_POST, 'no_moving', FILTER_SANITIZE_FULL_SPECIAL_CHARS)]
                        : ['no_sj' => $no_sj];
                    $afterReferences = $beforeReferences;
                    break;
                case 'payment':
                    $documentType = 'payment';
                    $referenceType = 'payment_id';
                    $beforeReferences = ['payment_id' => filter_input(INPUT_POST, 'payment_id', FILTER_SANITIZE_FULL_SPECIAL_CHARS)];
                    $afterReferences = $beforeReferences;
                    break;
                case 'repack':
                case 'moving':
                    $documentType = $data;
                    $referenceType = 'no_' . $data;
                    $beforeReferences = [$referenceType => filter_input(INPUT_POST, 'old_' . $data, FILTER_SANITIZE_FULL_SPECIAL_CHARS)];
                    $afterReferences = [$referenceType => filter_input(INPUT_POST, $referenceType, FILTER_SANITIZE_FULL_SPECIAL_CHARS)];
                    break;
                default:
                    throw new InvalidArgumentException('Unsupported document type.');
            }
            $before = getUserActionSnapshot($documentType, $beforeReferences, true);
            if ($before === null) throw new RuntimeException('Original document not found.');

            // Reject incomplete product replacements before deleting the old rows.
            if (in_array($data, ['slip', 'repack', 'moving', 'invoice'], true)) {
                $groups = $data === 'repack'
                    ? [['kd_awal', 'qty_awal', 'uom_awal'], ['kd_akhir', 'qty_akhir', 'uom_akhir']]
                    : ($data === 'invoice' ? [['kd', 'price_per_uom']] : [['kd', 'qty', 'uom']]);
                foreach ($groups as $fields) {
                    $count = null;
                    foreach ($fields as $field) {
                        $values = $_POST[$field] ?? null;
                        if (!is_array($values) || !$values || !array_is_list($values)
                            || ($count !== null && count($values) !== $count)) {
                            throw new RuntimeException('Incomplete product submission.');
                        }
                        $count = count($values);
                        foreach ($values as $value) {
                            if (!is_scalar($value) || trim((string)$value) === '') {
                                throw new RuntimeException('Incomplete product submission.');
                            }
                        }
                    }
                }
            }

        switch($data){
            case "slip":
                $productCodes = filter_input_array(INPUT_POST)["kd"];
                $productNames = filter_input_array(INPUT_POST)["material"];
                $qtys = filter_input_array(INPUT_POST)["qty"];
                $uoms = filter_input_array(INPUT_POST)["uom"];
                $notes = ($_POST["note"] ?? []);
                
                $currentOrderProducts = $before['products'];
                $productPrices = [];
                foreach ($currentOrderProducts as $product) {
                    $productPrices[$product['productCode']] = $product['price_per_UOM'];
                }
            
                if (deleteOrderProducts($old_sj, "order") !== true) throw new RuntimeException('Document update failed.');
            
                if ($pageState == "amend_slip_in") {
                    $result = updateOrderWithDependencies($no_sj, $storageCode, $no_LPB, $no_truk, $vendorCode, "NON", $order_date, $purchase_order, $old_sj);
                    if ($result !== true) throw new RuntimeException('Slip update failed.');
                    if ($result === true) {
                        for ($i = 0; $i < count($productCodes); $i++) {
                            $price = isset($productPrices[$productCodes[$i]]) ? $productPrices[$productCodes[$i]] : 0;
                            addOrderProducts($no_sj, $productCodes[$i], $qtys[$i], $uoms[$i], $price, ($notes[$i] ?? ''), "in");
                        }
                    }
                } else if ($pageState == "amend_slip_out") {
                    $result = updateOrderWithDependencies($no_sj, $storageCode, $no_LPB, $no_truk, "NON", $customerCode, $order_date, $purchase_order, $old_sj);
                    if ($result !== true) throw new RuntimeException('Slip update failed.');
                    if ($result === true) {
                        for ($i = 0; $i < count($productCodes); $i++) {
                            $price = isset($productPrices[$productCodes[$i]]) ? $productPrices[$productCodes[$i]] : 0;
                            addOrderProducts($no_sj, $productCodes[$i], $qtys[$i], $uoms[$i], $price, ($notes[$i] ?? ''), "out");
                        }
                    }
                } else {
                    $result = updateOrderWithDependencies($no_sj, $storageCode, $no_LPB, $no_truk, "NON", $customerCode, $order_date, $purchase_order, $old_sj);
                    if ($result !== true) throw new RuntimeException('Slip update failed.');
                    if ($result === true) {
                        for ($i = 0; $i < count($productCodes); $i++) {
                            $price = isset($productPrices[$productCodes[$i]]) ? $productPrices[$productCodes[$i]] : 0;
                            addOrderProducts($no_sj, $productCodes[$i], $qtys[$i], $uoms[$i], $price, ($notes[$i] ?? ''), "out_tax");
                        }
                    }
                }
                break;
            
            case "invoice":
                $productCodes = filter_input_array(INPUT_POST)["kd"];
                $productNames = filter_input_array(INPUT_POST)["material"];
                $qtys = filter_input_array(INPUT_POST)["qty"];
                $uoms = filter_input_array(INPUT_POST)["uom"];
                $notes = ($_POST["note"] ?? []);
                
                $invoice_date = filter_input(INPUT_POST, "invoice_date", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $no_invoice = filter_input(INPUT_POST, "no_invoice", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $no_faktur = filter_input(INPUT_POST, "no_faktur", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $price_per_uom = filter_input_array(INPUT_POST)["price_per_uom"];
                $tax = filter_input(INPUT_POST, "tax", FILTER_SANITIZE_FULL_SPECIAL_CHARS);

                if($pageState == "amend_invoice_moving"){
                    $no_moving = filter_input(INPUT_POST, "no_moving", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                    if (updateInvoice("-", $invoice_date, $no_invoice, $no_faktur, $no_moving, $tax) !== true) throw new RuntimeException('Document update failed.');
                    for($i = 0; $i < count($productCodes); $i++){
                        if (updatePriceForProductsMoving($no_moving, $productCodes[$i], $price_per_uom[$i]) !== true) throw new RuntimeException('Document update failed.');
                    }
                }
                else{
                    if (updateInvoice($no_sj, $invoice_date, $no_invoice, $no_faktur, "-", $tax) !== true) throw new RuntimeException('Document update failed.');
                    for($i = 0; $i < count($productCodes); $i++){
                        if (updatePriceForProducts($no_sj, $productCodes[$i], $price_per_uom[$i]) !== true) throw new RuntimeException('Document update failed.');
                    }
                }
                break;
            case "payment":
                $payment_id = filter_input(INPUT_POST, "payment_id", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $payment_date = filter_input(INPUT_POST, "payment_date", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $payment_amount = filter_input(INPUT_POST, "payment_amount", FILTER_SANITIZE_FULL_SPECIAL_CHARS);

                if($pageState == "amend_payment_moving"){
                    $no_moving = filter_input(INPUT_POST, "no_moving", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                    if (updatePayment("-", $payment_date, $payment_amount, $no_moving, $payment_id) !== true) throw new RuntimeException('Document update failed.');
                }
                else{
                    if (updatePayment($no_sj, $payment_date, $payment_amount, "-", $payment_id) !== true) throw new RuntimeException('Document update failed.');
                }
                break;
            case "repack":
                $repack_date = filter_input(INPUT_POST, "repack_date", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $no_repack = filter_input(INPUT_POST, "no_repack", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $old_repack = filter_input(INPUT_POST, "old_repack", FILTER_SANITIZE_FULL_SPECIAL_CHARS);

                $kd_awal = filter_input_array(INPUT_POST)["kd_awal"];
                $qty_awal = filter_input_array(INPUT_POST)["qty_awal"];
                $uom_awal = filter_input_array(INPUT_POST)["uom_awal"];
                $note_awal = ($_POST["note_awal"] ?? []);

                $kd_akhir = filter_input_array(INPUT_POST)["kd_akhir"];
                $qty_akhir = filter_input_array(INPUT_POST)["qty_akhir"];
                $uom_akhir = filter_input_array(INPUT_POST)["uom_akhir"];
                $note_akhir = ($_POST["note_akhir"] ?? []);

                if (deleteOrderProducts($old_repack, "repack") !== true) throw new RuntimeException('Document update failed.');
                if (updateRepack($no_repack, $repack_date, $storageCode, $old_repack) !== true) throw new RuntimeException('Document update failed.');
                for($i = 0; $i < count($kd_awal); $i++){
                    addOrderProducts($no_repack, $kd_awal[$i], $qty_awal[$i], $uom_awal[$i], 0, ($note_awal[$i] ?? ''), "repack_awal");
                }
                for($i = 0; $i < count($kd_akhir); $i++){
                    addOrderProducts($no_repack, $kd_akhir[$i], $qty_akhir[$i], $uom_akhir[$i], 0, ($note_akhir[$i] ?? ''), "repack_akhir");
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

                if (deleteOrderProducts($old_moving, "moving") !== true) throw new RuntimeException('Document update failed.');
                if (updateMoving($no_moving, $moving_date, $storageCodeSender, $storageCodeReceiver, $old_moving) !== true) throw new RuntimeException('Document update failed.');
                for($i = 0; $i < count($productCodes); $i++){
                    addOrderProducts($no_moving, $productCodes[$i], $qtys[$i], $uoms[$i], 0, "", "moving");
                }
                break;
        }
            $after = getUserActionSnapshot($documentType, $afterReferences, true);
            if ($after === null) throw new RuntimeException('Updated document not found.');
            createUserAction('UPDATE', $documentType, $referenceType,
                $after['references'][$referenceType], $before, $after,
                ['before' => $before['references'], 'after' => $after['references']]);
            $db->commit();
            header('Location: ../controller/index.php?' . http_build_query([
                'action' => 'show_amends', 'state' => $data, 'msg' => 'Record updated'
            ]));
        } catch (Throwable $error) {
            if ($db->inTransaction()) $db->rollBack();
            error_log('Document update (' . $data . '): ' . $error->getMessage());
            header('Location: ../controller/index.php?' . http_build_query([
                'action' => 'show_amends', 'state' => $data,
                'msg' => 'Error, document could not be updated. No changes were saved.'
            ]));
        }

        break;
    
    case "amend_delete_data":
        $data = filter_input(INPUT_POST, "data", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $code = filter_input(INPUT_POST, "code", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    
        if (!checkAccess($data, $userType)) {
            header("Location:../controller/index.php?action=dashboard");
            exit;
        }
    
        try {
            $db->beginTransaction();
            if (!is_string($code) || trim($code) === '' || $code === '-') {
                throw new InvalidArgumentException('Invalid deletion reference.');
            }
            $deleteSnapshots = [];
            $isMoving = strpos($code, 'SJP') !== false;
            switch ($data) {
                case 'slip':
                    $rows = usersActionFetchRows('SELECT status_mode FROM orders WHERE nomor_surat_jalan = ?', [$code], true);
                    $type = [1 => 'slip_in', 2 => 'slip_out', 3 => 'slip_tax'][$rows[0]['status_mode'] ?? 0] ?? null;
                    if ($type === null) throw new RuntimeException('Slip not found.');
                    $referenceType = $type === 'slip_in' ? 'no_LPB' : 'no_sj';
                    $references = ['no_sj' => $code];
                    break;
                case 'invoice':
                    $type = 'invoice';
                    $referenceType = 'no_invoice';
                    $references = [$isMoving ? 'no_moving' : 'no_sj' => $code];
                    break;
                case 'payment':
                    // The existing delete operation removes all payments for this document.
                    $column = $isMoving ? 'no_moving' : 'nomor_surat_jalan';
                    $payments = usersActionFetchRows('SELECT payment_id FROM payments WHERE ' . $column . ' = ?', [$code], true);
                    foreach ($payments as $payment) {
                        $snapshot = getUserActionSnapshot('payment', ['payment_id' => $payment['payment_id']], true);
                        if ($snapshot === null) throw new RuntimeException('Payment not found.');
                        $deleteSnapshots[] = ['payment_id', $snapshot];
                    }
                    break;
                case 'repack':
                case 'moving':
                    $type = $data;
                    $referenceType = 'no_' . $data;
                    $references = [$referenceType => $code];
                    break;
                default:
                    throw new InvalidArgumentException('Unsupported deletion type.');
            }
            if ($data !== 'payment') {
                $snapshot = getUserActionSnapshot($type, $references, true);
                if ($snapshot === null) throw new RuntimeException('Document not found.');
                // Distinguish records removed by this operation from retained context.
                $snapshot['deleted_related_records'] = $data === 'slip'
                    ? ['invoices', 'payments'] : ($data === 'invoice' ? ['payments'] : []);
                $deleteSnapshots[] = [$referenceType, $snapshot];
            }
            if (!$deleteSnapshots) throw new RuntimeException('No records found to delete.');
            $success = false;
    
            switch ($data) {
                case "slip":
                    $success = deleteOrderProducts($code, "order");
    
                    if ($success === true) {
                        $success = deleteMultiPayment($code, $data !== 'slip' && $isMoving);
                    }
    
                    if ($success === true) {
                        $success = deleteInvoice($code);
                    }
    
                    if ($success === true) {
                        $success = deleteOrder($code);
                    }
                    break;
    
                case "invoice":
                    $success = deleteMultiPayment($code, $data !== 'slip' && $isMoving);
    
                    if ($success === true) {
                        if($isMoving){
                            $result = $snapshot['products'];
                            foreach ($result as $key) {
                                $success = updatePriceForProductsMoving($code, $key["productCode"], 0);
                                if ($success !== true) break;
                            }
                        }
                        else{
                            $result = $snapshot['products'];
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
                    $success = deleteMultiPayment($code, $data !== 'slip' && $isMoving);
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
                foreach ($deleteSnapshots as [$referenceType, $before]) {
                    createUserAction('DELETE', $before['document_type'], $referenceType,
                        $before['references'][$referenceType], $before, null, $before['references']);
                }
                $db->commit();
                header("Location:../controller/index.php?action=show_amends&state=" . $data . "&msg=record deleted");
            } elseif ($success === 'foreign_key') {
                throw new Exception("Foreign key constraint violation. Deletion not allowed.");
            } else {
                throw new Exception("An error occurred while deleting the record.");
            }
        } catch (Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            error_log('Document deletion (' . $data . '): ' . $e->getMessage());
            header('Location: ../controller/index.php?' . http_build_query([
                'action' => 'show_amends', 'state' => $data,
                'msg' => 'Error, document could not be deleted. No changes were saved.'
            ]));
        }
        break;
        
    case "amendDelete":
        $no_id = filter_input(INPUT_GET, "no_id", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $state = filter_input(INPUT_GET, "state", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        break;
}
?>