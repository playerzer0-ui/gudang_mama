<?php 

    require_once "database.php";


    function create_payment($nomor_surat_jalan, $payment_date, $payment_amount, $no_moving){
        global $db;
    
        $query = 'INSERT INTO payments
            VALUES (:nomor_surat_jalan, :payment_date, :payment_amount, :no_moving, UUID())';
    
        $statement = $db->prepare($query);
        $statement->bindValue(":nomor_surat_jalan", $nomor_surat_jalan);
        $statement->bindValue(":payment_date", $payment_date);
        $statement->bindValue(":payment_amount", $payment_amount);
        $statement->bindValue(":no_moving", $no_moving);
    
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

    function getPaymentByID($payment_id){
        global $db;

        $query = "SELECT * FROM payments WHERE payment_id = :payment_id";
        $statement = $db->prepare($query);
        $statement->bindValue(":payment_id", $payment_id);

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

        if(!strpos($nomor_surat_jalan, "SJP")){
            $query = "SELECT SUM(payment_amount) AS totalPayment FROM payments WHERE nomor_surat_jalan = :nomor_surat_jalan";
        }
        else{
            $query = "SELECT SUM(payment_amount) AS totalPayment FROM payments WHERE no_moving = :nomor_surat_jalan";
        }
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

    function updatePayment($nomor_surat_jalan, $payment_date, $payment_amount, $no_moving, $payment_id){
        global $db;
    
        $query = "UPDATE payments SET payment_date = :payment_date, payment_amount = :payment_amount, nomor_surat_jalan = :nomor_surat_jalan, no_moving = :no_moving WHERE payment_id = :payment_id";
        
        $statement = $db->prepare($query);
        $statement->bindValue(":payment_date", $payment_date);
        $statement->bindValue(":payment_amount", $payment_amount);
        $statement->bindValue(":nomor_surat_jalan", $nomor_surat_jalan);
        $statement->bindValue(":no_moving", $no_moving);
        $statement->bindValue(":payment_id", $payment_id);
    
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

    function deletePayment($payment_id){
        global $db;
    
        $query = "DELETE FROM payments WHERE payment_id = :payment_id";
        $statement = $db->prepare($query);
        $statement->bindValue(":payment_id", $payment_id);
    
        try {
            $statement->execute();
            $statement->closeCursor();
            return true;
        } catch (PDOException $ex) {
            $errorCode = $ex->getCode();
            if ($errorCode == 23000) {
                $errorInfo = $ex->errorInfo;
                if (strpos($errorInfo[2], 'foreign key constraint fails') !== false) {
                    throw new Exception($ex->getMessage());
                }
            }
            return false;
        }
    }    

    function deleteMultiPayment($nomor_surat_jalan){
        global $db;
    
        $query = "DELETE FROM payments WHERE nomor_surat_jalan = :nomor_surat_jalan";
        $statement = $db->prepare($query);
        $statement->bindValue(":nomor_surat_jalan", $nomor_surat_jalan);
    
        try {
            $statement->execute();
            $statement->closeCursor();
            return true;
        } catch (PDOException $ex) {
            $errorCode = $ex->getCode();
            if ($errorCode == 23000) {
                $errorInfo = $ex->errorInfo;
                if (strpos($errorInfo[2], 'foreign key constraint fails') !== false) {
                    throw new Exception($ex->getMessage());
                }
            }
            return false;
        }
    }    
?>