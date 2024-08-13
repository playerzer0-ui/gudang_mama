<?php

    require_once "database.php";

    function generate_SJP($storageCode, $month, $year){
        global $db;
    
        // Get the count of existing numbers
        $query = 'SELECT count(*) AS totalIN FROM movings WHERE month(moving_date) = :mon AND year(moving_date) = :yea AND storageCodeSender = :storageCode';
        $statement = $db->prepare($query);
        $statement->bindValue(":mon", $month);
        $statement->bindValue(":yea", $year);
        $statement->bindValue(":storageCode", $storageCode);
    
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
    
        // Check for existing number and increment if necessary
        do {
            $generatedNo = $no . "/SJP/" . $storageCode . "/" . $month . "/" . $year;
            $checkQuery = 'SELECT COUNT(*) AS existingCount FROM movings WHERE no_moving = :generatedNo';
            
            $checkStmt = $db->prepare($checkQuery);
            $checkStmt->bindValue(":generatedNo", $generatedNo);
            $checkStmt->execute();
            $checkResult = $checkStmt->fetch(PDO::FETCH_ASSOC);
            $checkStmt->closeCursor();
            
            if($checkResult["existingCount"] > 0){
                $no++;
            } else {
                break;
            }
        } while(true);
    
        return $generatedNo;
    }    

    function getMovingByCode($no_moving){
        global $db;

        $query = "SELECT * FROM movings WHERE no_moving = :no_moving";
        $statement = $db->prepare($query);
        $statement->bindValue(":no_moving", $no_moving);

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

    function getAllMovings(){
        global $db;

        $query = "SELECT no_moving AS nomor_surat_jalan, moving_date, storageCodeSender, storageCodeReceiver FROM movings WHERE no_moving != '-'";
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

    function create_moving($no_moving, $moving_date, $storageCodeSender, $storageCodeReceiver){
        global $db;

        $query = 'INSERT INTO movings VALUES (:no_moving, :moving_date, :storageCodeSender, :storageCodeReceiver)';
        $statement = $db->prepare($query);
        $statement->bindValue(":no_moving", $no_moving);
        $statement->bindValue(":moving_date", $moving_date);
        $statement->bindValue(":storageCodeSender", $storageCodeSender);
        $statement->bindValue(":storageCodeReceiver", $storageCodeReceiver);

        try {
            $statement->execute();
        }
        catch(PDOException $ex){
            $ex->getMessage();
        }

        $statement->closeCursor();
    }

    function updateMoving($no_moving, $moving_date, $storageCodeSender, $storageCodeReceiver, $old_moving){
        global $db;
    
        $query = "UPDATE movings SET no_moving = :no_moving, moving_date = :moving_date, storageCodeSender = :storageCodeSender, storageCodeReceiver = :storageCodeReceiver WHERE no_moving = :old_moving";
        $statement = $db->prepare($query);
        $statement->bindValue(":no_moving", $no_moving);
        $statement->bindValue(":moving_date", $moving_date);
        $statement->bindValue(":storageCodeSender", $storageCodeSender);
        $statement->bindValue(":storageCodeReceiver", $storageCodeReceiver);
        $statement->bindValue(":old_moving", $old_moving);
    
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

    function deleteMoving($no_sj){
        global $db;
    
        $query = "DELETE FROM movings WHERE no_moving = :no_sj";
        $statement = $db->prepare($query);
        $statement->bindValue(":no_sj", $no_sj);
    
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
                    throw new Exception($ex->getMessage());
                }
            }
            return false;
        }
    }

?>