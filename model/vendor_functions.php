<?php 

require_once "database.php";

/**
 * Retrieves all vendors from the `vendors` table.
 *
 * This function fetches all records from the `vendors` table and returns them as an associative array.
 *
 * @return array An associative array of vendor records, where each record is an associative array of vendor details.
 * @throws Exception If there is an issue with database interaction.
 */
function getAllVendors(){
    global $db;

    $query = "SELECT * FROM vendors";
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
 * Retrieves the column names of the `vendors` table.
 *
 * This function fetches one record from the `vendors` table to get the column names.
 *
 * @return array An array of column names from the `vendors` table.
 * @throws Exception If there is an issue with database interaction.
 */
function getAllVendorsKeyNames(){
    global $db;

    $query = "SELECT * FROM vendors LIMIT 1";
    $statement = $db->prepare($query);

    try {
        $statement->execute();
    }
    catch(PDOException $ex){
        $ex->getMessage();
    }

    $result = $statement->fetchAll(PDO::FETCH_ASSOC);
    $statement->closeCursor();

    return array_keys($result[0]);
}

/**
 * Inserts a new vendor into the `vendors` table.
 *
 * This function adds a new record to the `vendors` table with the provided details. If the operation is successful, it returns true.
 *
 * @param string $vendorCode The unique code for the vendor.
 * @param string $vendorName The name of the vendor.
 * @param string $vendorAddress The address of the vendor.
 * @param string $vendorNPWP The NPWP (tax identification number) of the vendor.
 *
 * @return bool True if the vendor was successfully created, false otherwise.
 * @throws Exception If there is an issue with database interaction.
 */
function createVendor($vendorCode, $vendorName, $vendorAddress, $vendorNPWP){
    global $db;

    $query = "INSERT INTO vendors VALUES (:vendorCode, :vendorName, :vendorAddress, :vendorNPWP)";
    $statement = $db->prepare($query);
    $statement->bindValue(":vendorCode", $vendorCode);
    $statement->bindValue(":vendorName", $vendorName);
    $statement->bindValue(":vendorAddress", $vendorAddress);
    $statement->bindValue(":vendorNPWP", $vendorNPWP);

    try {
        $statement->execute();
    } catch (PDOException $ex) {
        return false;
    }
    $statement->closeCursor();
    return true;
}

/**
 * Updates an existing vendor in the `vendors` table.
 *
 * This function updates the details of a vendor identified by `oldCode` with new values. If successful, it returns true. 
 * Handles errors such as duplicate entries and foreign key constraints.
 *
 * @param string $vendorCode The new unique code for the vendor.
 * @param string $vendorName The new name of the vendor.
 * @param string $vendorAddress The new address of the vendor.
 * @param string $vendorNPWP The new NPWP of the vendor.
 * @param string $oldCode The current unique code of the vendor to be updated.
 *
 * @return bool|string True if the vendor was successfully updated, 'duplicate' if there's a duplicate entry, 
 * 'foreign_key' if there's a foreign key constraint error, or false otherwise.
 * @throws Exception If there is an issue with database interaction.
 */
function updateVendor($vendorCode, $vendorName, $vendorAddress, $vendorNPWP, $oldCode){
    global $db;

    $query = "UPDATE vendors SET vendorCode = :vendorCode, vendorName = :vendorName, vendorAddress = :vendorAddress, vendorNPWP = :vendorNPWP WHERE vendorCode = :oldCode";
    $statement = $db->prepare($query);
    $statement->bindValue(":vendorCode", $vendorCode);
    $statement->bindValue(":vendorName", $vendorName);
    $statement->bindValue(":vendorAddress", $vendorAddress);
    $statement->bindValue(":vendorNPWP", $vendorNPWP);
    $statement->bindValue(":oldCode", $oldCode);

    try {
        $statement->execute();
        $statement->closeCursor();
        return true;
    } catch (PDOException $ex) {
        $errorCode = $ex->getCode();
        // MySQL error code for duplicate entry
        if ($errorCode == 23000) {
            // Duplicate entry or foreign key constraint error
            $errorInfo = $ex->errorInfo;
            if (strpos($errorInfo[2], 'Duplicate entry') !== false) {
                return 'duplicate';
            } elseif (strpos($errorInfo[2], 'foreign key constraint fails') !== false) {
                return 'foreign_key';
            }
        }
        return false;
    }
}

/**
 * Deletes a vendor from the `vendors` table.
 *
 * This function removes a vendor record from the `vendors` table based on the provided vendor code. 
 * Handles foreign key constraint errors and returns an appropriate response.
 *
 * @param string $vendorCode The unique code of the vendor to be deleted.
 *
 * @return bool|string True if the vendor was successfully deleted, 'foreign_key' if there's a foreign key constraint error, or false otherwise.
 * @throws Exception If there is an issue with database interaction.
 */
function deleteVendor($vendorCode){
    global $db;

    $query = "DELETE FROM vendors WHERE vendorCode = :vendorCode";
    $statement = $db->prepare($query);
    $statement->bindValue(":vendorCode", $vendorCode);

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
 * Retrieves a vendor's details by their unique code.
 *
 * This function fetches a single record from the `vendors` table based on the provided vendor code.
 *
 * @param string $vendorCode The unique code of the vendor to retrieve.
 *
 * @return array|null An associative array of vendor details if found, or null if no vendor matches the provided code.
 * @throws Exception If there is an issue with database interaction.
 */
function getVendorByCode($vendorCode){
    global $db;

    $query = "SELECT * FROM vendors WHERE vendorCode = :vendorCode";
    $statement = $db->prepare($query);
    $statement->bindValue(":vendorCode", $vendorCode);

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