<?php

    require_once "database.php";

    function generate_SJR($storageCode, $month, $year){
        global $db;
    
        // Get the count of existing numbers
        $query = 'SELECT count(*) AS totalIN FROM repacks WHERE month(repack_date) = :mon AND year(repack_date) = :yea AND storageCode = :storageCode';
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
            $generatedNo = $no . "/SJR/" . $storageCode . "/" . $month . "/" . $year;
            $checkQuery = 'SELECT COUNT(*) AS existingCount FROM repacks WHERE no_SJR = :generatedNo';
            
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

    function getRepackByCode($no_repack){
        global $db;

        $query = "SELECT * FROM repacks WHERE no_repack = :no_repack";
        $statement = $db->prepare($query);
        $statement->bindValue(":no_repack", $no_repack);

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

    function getAllRepacks(){
        global $db;

        $query = "SELECT no_repack AS nomor_surat_jalan, repack_date, storageCode FROM repacks WHERE no_repack != '-'";
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

    function create_repack($storageCode, $repack_date, $no_repack){
        global $db;

        $query = 'INSERT INTO repacks VALUES (:no_repack, :repack_date, :storageCode)';
        $statement = $db->prepare($query);
        $statement->bindValue(":no_repack", $no_repack);
        $statement->bindValue(":repack_date", $repack_date);
        $statement->bindValue(":storageCode", $storageCode);

        try {
            $statement->execute();
        }
        catch(PDOException $ex){
            $ex->getMessage();
        }

        $statement->closeCursor();
    }

    function updateRepack($no_repack, $repack_date, $storageCode, $old_repack){
        global $db;

        $query = "UPDATE repacks SET no_repack = :no_repack, repack_date = :repack_date, storageCode = :storageCode WHERE no_repack = :old_repack";
        $statement = $db->prepare($query);
        $statement->bindValue(":no_repack", $no_repack);
        $statement->bindValue(":repack_date", $repack_date);
        $statement->bindValue(":storageCode", $storageCode);
        $statement->bindValue(":old_repack", $old_repack);

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

    function deleteRepack($no_sj){
        global $db;
    
        $query = "DELETE FROM repacks WHERE no_repack = :no_sj";
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
                    return 'foreign_key';
                }
            }
            return false;
        }
    }

?>