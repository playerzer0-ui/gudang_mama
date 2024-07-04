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

?>