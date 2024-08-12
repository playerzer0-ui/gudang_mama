<?php

    require_once "database.php";
    require_once "../model/order_products_functions.php";

    function create_invoice($nomor_surat_jalan, $invoice_date, $no_invoice, $no_faktur, $no_moving, $tax){
        global $db;
    
        $query = 'INSERT INTO invoices
            VALUES (:nomor_surat_jalan, :invoice_date, :no_invoice, :no_faktur, :no_moving, :tax)';
    
        $statement = $db->prepare($query);
        $statement->bindValue(":nomor_surat_jalan", $nomor_surat_jalan);
        $statement->bindValue(":invoice_date", $invoice_date);
        $statement->bindValue(":no_invoice", $no_invoice);
        $statement->bindValue(":no_faktur", $no_faktur);
        $statement->bindValue(":no_moving", $no_moving);
        $statement->bindValue(":tax", $tax);
    
        try {
            $statement->execute();
        }
        catch(PDOException $ex){
            $ex->getMessage();
        }
    
        $statement->closeCursor();
    }

    function getAllInvoices(){
        global $db;

        $query = "SELECT * FROM invoices";
        $statement = $db->prepare($query);

        try {
            $statement->execute();
        }
        catch(PDOException $ex){
            $ex->getMessage();
        }
    
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        $statement->closeCursor();
        return $result;
    }

    function generateNoInvoice($storageCode, $month, $year){
        global $db;
    
        // Retrieve all existing invoice numbers for the specified month, year, and storage code
        $query = 'SELECT no_invoice FROM invoices 
                  WHERE month(invoice_date) = :mon AND year(invoice_date) = :yea AND no_invoice LIKE :storageCode
                  ORDER BY no_invoice';
    
        $statement = $db->prepare($query);
        $statement->bindValue(":mon", $month);
        $statement->bindValue(":yea", $year);
        $statement->bindValue(":storageCode", "%/INV/" . $storageCode . "/" . $month . "/" . $year);
    
        try {
            $statement->execute();
        } catch (PDOException $ex) {
            $ex->getMessage();
        }
    
        $existingInvoices = $statement->fetchAll(PDO::FETCH_COLUMN);
        $statement->closeCursor();
    
        // Initialize the number
        $no = 1;
        $invoiceNumbers = array_map(function($invoice) {
            // Extract the numeric part of the invoice number
            return (int) explode('/', $invoice)[0];
        }, $existingInvoices);
    
        // Find the smallest available number
        while (in_array($no, $invoiceNumbers)) {
            $no++;
        }
    
        if ($month < 10) {
            $month = "0" . $month;
        }
    
        // Return the new invoice number
        return $no . "/INV/" . $storageCode . "/" . $month . "/" . $year;
    }    

    function invoiceExists($invoiceNo) {
        global $db;
    
        $query = 'SELECT COUNT(*) FROM invoices WHERE no_invoice = :invoiceNo';
    
        $statement = $db->prepare($query);
        $statement->bindValue(':invoiceNo', $invoiceNo);
    
        try {
            $statement->execute();
        } catch (PDOException $ex) {
            $ex->getMessage();
        }
    
        $exists = $statement->fetchColumn() > 0;
    
        $statement->closeCursor();
    
        return $exists;
    }

    function getInvoiceByNoSJ($nomor_surat_jalan, $no_moving){
        global $db;

        if($no_moving == null){
            $query = "SELECT * FROM invoices WHERE nomor_surat_jalan = :nomor_surat_jalan";
            $statement = $db->prepare($query);
            $statement->bindValue(":nomor_surat_jalan", $nomor_surat_jalan);
        }
        else{
            $query = "SELECT * FROM invoices WHERE no_moving = :no_moving";
            $statement = $db->prepare($query);
            $statement->bindValue(":no_moving", $no_moving);
        }

        try {
            $statement->execute();
        }
        catch(PDOException $ex){
            $ex->getMessage();
        }
    
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        $statement->closeCursor();
        return $result;
    }

    function getHutangDetails($month, $year, $storageCode, $mode){
        global $db;

        if($mode == "hutang"){
            $query = 'SELECT 
                o.nomor_surat_jalan,
                i.invoice_date, 
                i.no_invoice,
                i.tax,
                v.vendorName, 
                COALESCE(p.payment_date, "-") AS payment_date, 
                COALESCE(p.payment_amount, 0) AS payment_amount
            FROM 
                orders o
            JOIN 
                invoices i ON o.nomor_surat_jalan = i.nomor_surat_jalan
            JOIN 
                vendors v ON o.vendorCode = v.vendorCode
            LEFT JOIN 
                payments p ON o.nomor_surat_jalan = p.nomor_surat_jalan
            WHERE 
                MONTH(i.invoice_date) = :mon
                AND YEAR(i.invoice_date) = :yea
                AND o.storageCode = :storageCode
                AND o.status_mode = 1';
        }
        else{
            $query = 'SELECT 
                o.nomor_surat_jalan,
                i.invoice_date, 
                i.no_invoice, 
                i.tax,
                c.customerName, 
                COALESCE(p.payment_date, "-") AS payment_date, 
                COALESCE(p.payment_amount, 0) AS payment_amount
            FROM 
                orders o
            JOIN 
                invoices i ON o.nomor_surat_jalan = i.nomor_surat_jalan
            JOIN 
                customers c ON o.customerCode = c.customerCode
            LEFT JOIN 
                payments p ON o.nomor_surat_jalan = p.nomor_surat_jalan
            WHERE 
                MONTH(i.invoice_date) = :mon
                AND YEAR(i.invoice_date) = :yea
                AND o.storageCode = :storageCode
                AND o.status_mode = 2';
        }

        $statement = $db->prepare($query);
        $statement->bindValue(':mon', $month);
        $statement->bindValue(':yea', $year);
        $statement->bindValue(':storageCode', $storageCode);

        try {
            $statement->execute();
        } catch (PDOException $ex) {
            echo $ex->getMessage();
        }

        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        $statement->closeCursor();
        return $result;
    }

    function getLaporanHutangPiutang($month, $year, $storageCode, $mode) {
        $groupData = [];
        $hutangDetails = getHutangDetails($month, $year, $storageCode, $mode);
        
        if($mode == "hutang"){
            foreach($hutangDetails as $details){
                $hutangKey = $details["nomor_surat_jalan"];
                if(!isset($groupData[$hutangKey])){
                    $groupData[$hutangKey] = [
                        "invoice_date" => $details["invoice_date"],
                        "no_invoice" => $details["no_invoice"],
                        "tax" => $details["tax"],
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
        }
        else{
            foreach($hutangDetails as $details){
                $hutangKey = $details["nomor_surat_jalan"];
                if(!isset($groupData[$hutangKey])){
                    $groupData[$hutangKey] = [
                        "invoice_date" => $details["invoice_date"],
                        "no_invoice" => $details["no_invoice"],
                        "tax" => $details["tax"],
                        "customerName" => $details["customerName"],
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
        }

        return array_values($groupData);
    }

    function updateInvoice($nomor_surat_jalan, $invoice_date, $no_invoice, $no_faktur, $no_moving, $tax){
        global $db;
    
        if($no_moving == "-"){
            $query = "UPDATE invoices SET invoice_date = :invoice_date, no_invoice = :no_invoice, no_faktur = :no_faktur, tax = :tax WHERE nomor_surat_jalan = :nomor_surat_jalan";
            $statement = $db->prepare($query);
            $statement->bindValue(":invoice_date", $invoice_date);
            $statement->bindValue(":no_invoice", $no_invoice);
            $statement->bindValue(":no_faktur", $no_faktur);
            $statement->bindValue(":tax", $tax);
            $statement->bindValue(":nomor_surat_jalan", $nomor_surat_jalan);
        }
        else{
            $query = "UPDATE invoices SET invoice_date = :invoice_date, no_invoice = :no_invoice, no_faktur = :no_faktur, tax = :tax WHERE no_moving = :no_moving";
            $statement = $db->prepare($query);
            $statement->bindValue(":invoice_date", $invoice_date);
            $statement->bindValue(":no_invoice", $no_invoice);
            $statement->bindValue(":no_faktur", $no_faktur);
            $statement->bindValue(":tax", $tax);
            $statement->bindValue(":no_moving", $no_moving);
        }
    
        try {
            $statement->execute();
            $statement->closeCursor();
            return true;
        } catch (PDOException $ex) {
            $errorCode = $ex->getCode();
            // MySQL error code for foreign key constraint violation
            if ($errorCode == 23000) {
                // Foreign key constraint error
                $errorInfo = $ex->errorInfo;
                if (strpos($errorInfo[2], 'foreign key constraint fails') !== false) {
                    return 'foreign_key';
                }
            }
            return false;
        }
    }

    function deleteInvoice($no_sj){
        global $db;
    
        if(!strpos($no_sj, "SJP")){
            $query = "DELETE FROM invoices WHERE nomor_surat_jalan = :no_sj";
            $statement = $db->prepare($query);
            $statement->bindValue(":no_sj", $no_sj);
        }
        else{
            $query = "DELETE FROM invoices WHERE no_moving = :no_sj";
            $statement = $db->prepare($query);
            $statement->bindValue(":no_sj", $no_sj);
        }
    
        try {
            $statement->execute();
            $statement->closeCursor();
            return true;
        } catch (PDOException $ex) {
            $errorCode = $ex->getCode();
            // MySQL error code for foreign key constraint violation
            if ($errorCode == 23000) {
                // Foreign key constraint error
                $errorInfo = $ex->errorInfo;
                if (strpos($errorInfo[2], 'foreign key constraint fails') !== false) {
                    return 'foreign_key';
                }
            }
            return false;
        }
    }
?>