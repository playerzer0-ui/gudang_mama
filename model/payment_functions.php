<?php 

    require_once "database.php";


    function create_payment($nomor_surat_jalan, $payment_date, $payment_amount){
        global $db;
    
        $query = 'INSERT INTO payments
            VALUES (:nomor_surat_jalan, :payment_date, :payment_amount)';
    
        $statement = $db->prepare($query);
        $statement->bindValue(":nomor_surat_jalan", $nomor_surat_jalan);
        $statement->bindValue(":payment_date", $payment_date);
        $statement->bindValue(":payment_amount", $payment_amount);
    
        try {
            $statement->execute();
        }
        catch(PDOException $ex){
            $ex->getMessage();
        }
    
        $statement->closeCursor();
    }

    function getAllPayments(){
        global $db;

        $query = "SELECT * FROM payments";
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

    function getPaymentByNoSJ($nomor_surat_jalan){
        global $db;

        $query = "SELECT * FROM payments WHERE nomor_surat_jalan = :nomor_surat_jalan";
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

    function getTotalPayment($nomor_surat_jalan){
        global $db;

        $query = "SELECT SUM(payment_amount) AS totalPayment FROM payments WHERE nomor_surat_jalan = :nomor_surat_jalan";
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