<?php

    require_once "database.php";

    function generate_SJR($storageCode){
        global $db;

        $query = 'SELECT count(*) AS totalIN FROM repacks WHERE month(repack_date) = :mon AND year(repack_date) = :yea AND no_repack LIKE "%SJR%"';
        $statement = $db->prepare($query);
        $statement->bindValue(":mon", date("m"));
        $statement->bindValue(":yea", date("Y"));

        try {
            $statement->execute();
        }
        catch(PDOException $ex){
            $ex->getMessage();
        }

        $result = $statement->fetch(PDO::FETCH_ASSOC);
        $no = $result["totalIN"] + 1;

        $statement->closeCursor();

        return $no . "/SJR/" . $storageCode . "/" . date("m") . "/" . date("Y");
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

    function updateRepack($no_repack, $repack_date, $storageCode){
        global $db;

        $query = "UPDATE repacks SET repack_date = :repack_date, storageCode = :storageCode WHERE no_repack = :no_sj";
        $statement = $db->prepare($query);
        $statement->bindValue(":repack_date", $repack_date);
        $statement->bindValue(":storageCode", $storageCode);
        $statement->bindValue(":no_repack", $no_repack);

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