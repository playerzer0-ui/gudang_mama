<?php

    require_once "database.php";

    /**
     * Generates a unique moving number (SJP) for a given storage code, month, and year.
     *
     * The function calculates the next available moving number by checking existing records for the specified
     * month, year, and storage code. It ensures that the generated number is unique.
     *
     * @param string $storageCode The storage code for the moving.
     * @param int $month The month for which the moving number is generated.
     * @param int $year The year for which the moving number is generated.
     *
     * @return string Returns the generated moving number.
     */
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

    /**
     * Retrieves a moving record based on the provided moving number.
     *
     * This function fetches details of a moving record from the `movings` table using the provided moving number.
     *
     * @param string $no_moving The moving number to retrieve.
     *
     * @return array|null Returns an associative array containing the moving record details, or `null` if not found.
     */
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

    /**
     * Retrieves all moving records from the `movings` table.
     *
     * This function fetches all records from the `movings` table where the moving number is not equal to "-".
     *
     * @return array Returns an array of associative arrays, each containing details of a moving record.
     */
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

    /**
     * Inserts a new moving record into the `movings` table.
     *
     * This function adds a new record to the `movings` table with the specified moving number, date, and storage codes.
     *
     * @param string $no_moving The moving number to insert.
     * @param string $moving_date The date of the moving.
     * @param string $storageCodeSender The storage code of the sender.
     * @param string $storageCodeReceiver The storage code of the receiver.
     *
     * @return void
     */
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

    /**
     * Updates an existing moving record in the `movings` table.
     *
     * This function modifies an existing moving record based on the provided new moving number, date, and storage codes.
     * It identifies the record to update by the old moving number.
     *
     * @param string $no_moving The new moving number to update.
     * @param string $moving_date The new date of the moving.
     * @param string $storageCodeSender The new storage code of the sender.
     * @param string $storageCodeReceiver The new storage code of the receiver.
     * @param string $old_moving The old moving number to identify the record to update.
     *
     * @return bool Returns `true` if the update is successful, or `false` if an error occurs.
     * 
     * @throws string Returns 'foreign_key' if a foreign key constraint violation occurs.
     */
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

    /**
     * Deletes a moving record from the `movings` table.
     *
     * This function removes a record from the `movings` table based on the provided moving number.
     *
     * @param string $no_sj The moving number of the record to delete.
     *
     * @return bool Returns `true` if the deletion is successful, or `false` if an error occurs.
     * 
     * @throws Exception Throws an exception if a foreign key constraint violation occurs.
     */
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