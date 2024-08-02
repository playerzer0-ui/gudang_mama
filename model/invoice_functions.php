<?php

    require_once "database.php";
    require_once "../model/order_products_functions.php";

    function create_invoice($nomor_surat_jalan, $invoice_date, $no_invoice, $no_faktur){
        global $db;
    
        $query = 'INSERT INTO invoices
            VALUES (:nomor_surat_jalan, :invoice_date, :no_invoice, :no_faktur)';
    
        $statement = $db->prepare($query);
        $statement->bindValue(":nomor_surat_jalan", $nomor_surat_jalan);
        $statement->bindValue(":invoice_date", $invoice_date);
        $statement->bindValue(":no_invoice", $no_invoice);
        $statement->bindValue(":no_faktur", $no_faktur);
    
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
        
        $query = 'SELECT count(*) AS totalIN FROM invoices WHERE month(invoice_date) = :mon AND year(invoice_date) = :yea AND no_invoice LIKE :storageCode';

        $statement = $db->prepare($query);
        $statement->bindValue(":mon", $month);
        $statement->bindValue(":yea", $year);
        $statement->bindValue(":storageCode", "%" . $storageCode . "%");

        try {
            $statement->execute();
        }
        catch(PDOException $ex){
            $ex->getMessage();
        }
    
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        $no = $result["totalIN"] + 1;
    
        $statement->closeCursor();

        if($month < 10){
            $month = "0" . $month;
        }
    
        return $no . "/INV/" . $storageCode . "/" . $month . "/" . $year;
    }

    function getInvoiceByNoSJ($nomor_surat_jalan){
        global $db;

        $query = "SELECT * FROM invoices WHERE nomor_surat_jalan = :nomor_surat_jalan";
        $statement = $db->prepare($query);
        $statement->bindValue(":nomor_surat_jalan", $nomor_surat_jalan);

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

    function deleteInvoice($no_sj){
        global $db;
    
        $query = "DELETE FROM invoices WHERE nomor_surat_jalan = :no_sj";
        $statement = $db->prepare($query);
        $statement->bindValue(":no_sj", $no_sj);
    
        try {
            $statement->execute();
        }
        catch(PDOException $ex){
            $ex->getMessage();
        }
    
        $statement->closeCursor();
    }
?>