<?php

    require_once "database.php";

    function generate_SJP($storageCode){
        global $db;

        $query = 'SELECT count(*) AS totalIN FROM movings WHERE month(moving_date) = :mon AND year(moving_date) = :yea';
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

        return $no . "/SJP/" . $storageCode . "/" . date("m") . "/" . date("Y");
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
                    return 'foreign_key';
                }
            }
            return false;
        }
    }

?>