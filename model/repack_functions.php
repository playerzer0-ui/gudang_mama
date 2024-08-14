<?php

    require_once "database.php";

/**
 * Generates a new SJR (Surat Jalan Repacking) number for a given storage code, month, and year.
 *
 * This function generates a unique SJR number based on the count of existing numbers and ensures that the generated
 * number is not already in use. The number format is "count/SJR/storageCode/month/year".
 *
 * @param string $storageCode The code of the storage for which the SJR number is generated.
 * @param int $month The month for which the SJR number is generated.
 * @param int $year The year for which the SJR number is generated.
 *
 * @return string Returns the generated SJR number.
 */
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
        $checkQuery = 'SELECT COUNT(*) AS existingCount FROM repacks WHERE no_repack = :generatedNo';
        
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
 * Retrieves a repack record based on the provided repack number.
 *
 * This function fetches details of a repack record from the `repacks` table using the provided repack number.
 *
 * @param string $no_repack The number of the repack to retrieve.
 *
 * @return array|null Returns an associative array containing the repack record details, or `null` if not found.
 */
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

/**
 * Retrieves all repack records from the `repacks` table.
 *
 * This function fetches all repack records from the `repacks` table where the repack number is not '-'.
 *
 * @return array Returns an array of associative arrays, each containing details of a repack.
 */
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

/**
 * Inserts a new repack record into the `repacks` table.
 *
 * This function adds a new repack record with the specified storage code, repack date, and repack number to the `repacks` table.
 *
 * @param string $storageCode The code of the storage for the repack.
 * @param string $repack_date The date of the repack.
 * @param string $no_repack The number of the new repack.
 *
 * @return void This function does not return a value.
 */
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

/**
 * Updates an existing repack record in the `repacks` table.
 *
 * This function modifies an existing repack record based on the provided new repack details.
 * It identifies the record to update by the old repack number.
 *
 * @param string $no_repack The new repack number.
 * @param string $repack_date The new repack date.
 * @param string $storageCode The new storage code.
 * @param string $old_repack The current repack number of the record to update.
 *
 * @return bool|string Returns `true` if the update is successful, `false` if an error occurs,
 *                     or a string ('foreign_key') if a foreign key constraint error is encountered.
 */
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

/**
 * Deletes a specific repack record from the `repacks` table.
 *
 * This function removes a repack record from the `repacks` table based on the provided repack number.
 *
 * @param string $no_sj The number of the repack record to delete.
 *
 * @return bool|string Returns `true` if the deletion is successful, `false` if an error occurs,
 *                     or a string ('foreign_key') if a foreign key constraint violation is encountered.
 */
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
                throw new Exception($ex->getMessage());
            }
        }
        return false;
    }
}

?>