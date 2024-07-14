<?php

    require_once "database.php";

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

    function getLaporanHutang($month, $year, $storageCode) {
        global $db;
    
        $query = "SELECT 
            i.invoice_date, 
            i.no_invoice, 
            v.vendorName, 
            pr.productName, 
            op.qty, 
            op.price_per_UOM, 
            (op.qty * op.price_per_UOM) AS nominal, 
            COALESCE(p.payment_date, NULL) AS payment_date, 
            COALESCE(p.payment_amount, 0) AS payment_amount, 
            (COALESCE(p.payment_amount, 0) - (op.qty * op.price_per_UOM)) AS remaining
        FROM 
            orders o
        JOIN 
            invoices i ON o.nomor_surat_jalan = i.nomor_surat_jalan
        JOIN 
            vendors v ON o.vendorCode = v.vendorCode
        JOIN 
            order_products op ON o.nomor_surat_jalan = op.nomor_surat_jalan
        JOIN 
            products pr ON op.productCode = pr.productCode
        LEFT JOIN 
            payments p ON o.nomor_surat_jalan = p.nomor_surat_jalan
        WHERE 
            MONTH(i.invoice_date) = :mon
            AND YEAR(i.invoice_date) = :yea
            AND o.storageCode = :storageCode";
    
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
    
        // Group data by invoice
        $groupedData = [];
        foreach ($result as $row) {
            $invoiceKey = $row['invoice_date'] . '-' . $row['no_invoice'];
            if (!isset($groupedData[$invoiceKey])) {
                $groupedData[$invoiceKey] = [
                    'invoice_date' => $row['invoice_date'],
                    'no_invoice' => $row['no_invoice'],
                    'vendorName' => $row['vendorName'],
                    'payment_date' => $row['payment_date'],
                    'payment_amount' => $row['payment_amount'],
                    'totalQty' => 0,
                    'totalNominal' => 0,
                    'totalRemaining' => 0,
                    'rows' => []
                ];
            }
            $groupedData[$invoiceKey]['rows'][] = $row;
            $groupedData[$invoiceKey]['totalQty'] += $row['qty'];
            $groupedData[$invoiceKey]['totalNominal'] += $row['nominal'];
            $groupedData[$invoiceKey]['totalRemaining'] += $row['remaining'];
        }
    
        return array_values($groupedData);
    }    

    function getLaporanPiutang($month, $year) {
        global $db;
    
        $query = "SELECT 
            i.invoice_date, 
            i.no_invoice, 
            c.customerName, 
            pr.productName, 
            op.qty, 
            op.price_per_UOM, 
            (op.qty * op.price_per_UOM) AS nominal, 
            COALESCE(p.payment_date, NULL) AS payment_date, 
            COALESCE(p.payment_amount, 0) AS payment_amount, 
            (COALESCE(p.payment_amount, 0) - (op.qty * op.price_per_UOM)) AS remaining
        FROM 
            orders o
        JOIN 
            invoices i ON o.nomor_surat_jalan = i.nomor_surat_jalan
        JOIN 
            vendors v ON o.vendorCode = v.vendorCode
        JOIN 
            customers c ON o.customerCode = c.customerCode
        JOIN 
            order_products op ON o.nomor_surat_jalan = op.nomor_surat_jalan
        JOIN 
            products pr ON op.productCode = pr.productCode
        LEFT JOIN 
            payments p ON o.nomor_surat_jalan = p.nomor_surat_jalan
        WHERE 
            MONTH(i.invoice_date) = :mon
            AND YEAR(i.invoice_date) = :yea
            AND o.storageCode = 'NON'";
    
        $statement = $db->prepare($query);
        $statement->bindValue(':mon', $month);
        $statement->bindValue(':yea', $year);
    
        try {
            $statement->execute();
        } catch (PDOException $ex) {
            echo $ex->getMessage();
        }
    
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        $statement->closeCursor();
    
        // Group data by invoice
        $groupedData = [];
        foreach ($result as $row) {
            $invoiceKey = $row['invoice_date'] . '-' . $row['no_invoice'];
            if (!isset($groupedData[$invoiceKey])) {
                $groupedData[$invoiceKey] = [
                    'invoice_date' => $row['invoice_date'],
                    'no_invoice' => $row['no_invoice'],
                    'customerName' => $row['customerName'],
                    'payment_date' => $row['payment_date'],
                    'payment_amount' => $row['payment_amount'],
                    'totalQty' => 0,
                    'totalNominal' => 0,
                    'totalRemaining' => 0,
                    'rows' => []
                ];
            }
            $groupedData[$invoiceKey]['rows'][] = $row;
            $groupedData[$invoiceKey]['totalQty'] += $row['qty'];
            $groupedData[$invoiceKey]['totalNominal'] += $row['nominal'];
            $groupedData[$invoiceKey]['totalRemaining'] += $row['remaining'];
        }
    
        return array_values($groupedData);
    }
    

?>