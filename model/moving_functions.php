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

        $monthText = ($month < 10) ? "0" . $month : $month;
        $sequenceKey = $storageCode . "|" . $monthText . "|" . $year . "|SJP";

        $query = 'SELECT MAX(CAST(SUBSTRING_INDEX(no_moving, "/", 1) AS UNSIGNED)) AS max_no
                  FROM movings
                  WHERE month(moving_date) = :mon
                  AND year(moving_date) = :yea
                  AND storageCodeSender = :storageCode';

        $existingStmt = $db->prepare($query);
        $existingStmt->bindValue(':mon', $month);
        $existingStmt->bindValue(':yea', $year);
        $existingStmt->bindValue(':storageCode', $storageCode);
        $existingStmt->execute();
        $maxResult = $existingStmt->fetch(PDO::FETCH_ASSOC);
        $existingStmt->closeCursor();

        $sequenceStmt = $db->prepare('SELECT last_number FROM number_sequences WHERE sequence_key = :sequence_key');
        $sequenceStmt->bindValue(':sequence_key', $sequenceKey);
        $sequenceStmt->execute();
        $sequenceNumber = (int)$sequenceStmt->fetchColumn();
        $sequenceStmt->closeCursor();

        $nextNumber = max((int)($maxResult['max_no'] ?? 0), $sequenceNumber) + 1;
        return $nextNumber . "/SJP/" . $storageCode . "/" . $monthText . "/" . $year;
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

        $date = DateTime::createFromFormat('Y-m-d', $moving_date);
        $month = (int)$date->format('m');
        $year = (int)$date->format('Y');
        $monthText = ($month < 10) ? '0' . $month : (string)$month;
        $sequenceKey = $storageCodeSender . '|' . $monthText . '|' . $year . '|SJP';
        $generatedNumber = (int)explode('/', $no_moving)[0];

        $query = 'INSERT INTO movings VALUES (:no_moving, :moving_date, :storageCodeSender, :storageCodeReceiver)';
        $statement = $db->prepare($query);
        $statement->bindValue(":no_moving", $no_moving);
        $statement->bindValue(":moving_date", $moving_date);
        $statement->bindValue(":storageCodeSender", $storageCodeSender);
        $statement->bindValue(":storageCodeReceiver", $storageCodeReceiver);

        $db->beginTransaction();

        try {
            $statement->execute();
            $rowStmt = $db->prepare('SELECT last_number FROM number_sequences WHERE sequence_key = :sequence_key FOR UPDATE');
            $rowStmt->bindValue(':sequence_key', $sequenceKey);
            $rowStmt->execute();
            $currentNumber = (int)$rowStmt->fetchColumn();
            $rowStmt->closeCursor();

            $nextValue = max($currentNumber, $generatedNumber);
            $upsertStmt = $db->prepare('INSERT INTO number_sequences (sequence_key, last_number) VALUES (:sequence_key, :last_number)
                                        ON DUPLICATE KEY UPDATE last_number = GREATEST(last_number, VALUES(last_number))');
            $upsertStmt->bindValue(':sequence_key', $sequenceKey);
            $upsertStmt->bindValue(':last_number', $nextValue);
            $upsertStmt->execute();
            $upsertStmt->closeCursor();

            $db->commit();
            return true;
        } catch (PDOException $ex) {
            $db->rollBack();
            error_log($ex->getMessage());
            return false;
        }
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