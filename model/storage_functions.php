<?php 

require_once "database.php";

/**
 * Retrieves all storage records from the `storages` table.
 *
 * This function fetches all rows from the `storages` table and returns them as an associative array.
 *
 * @return array An array of associative arrays, each representing a storage record.
 */
function getAllStorages(){
    global $db;

    $query = "SELECT * FROM storages";
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
 * Retrieves the key names (column names) of the `storages` table.
 *
 * This function fetches a single row from the `storages` table to determine the column names,
 * then returns an array of these column names.
 *
 * @return array An array of column names from the `storages` table.
 */
function getAllStoragesKeyNames(){
    global $db;

    $query = "SELECT * FROM storages LIMIT 1";
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
 * Inserts a new storage record into the `storages` table.
 *
 * This function inserts a new record into the `storages` table with the given storage code, name,
 * address, and NPWP (tax identification number).
 *
 * @param string $storageCode The code of the storage.
 * @param string $storageName The name of the storage.
 * @param string $storageAddress The address of the storage.
 * @param string $storageNPWP The NPWP (tax identification number) of the storage.
 *
 * @return bool Returns `true` if the record was successfully inserted, otherwise `false`.
 */
function createStorage($storageCode, $storageName, $storageAddress, $storageNPWP){
    global $db;

    $query = "INSERT INTO storages VALUES (:storageCode, :storageName, :storageAddress, :storageNPWP)";
    $statement = $db->prepare($query);
    $statement->bindValue(":storageCode", $storageCode);
    $statement->bindValue(":storageName", $storageName);
    $statement->bindValue(":storageAddress", $storageAddress);
    $statement->bindValue(":storageNPWP", $storageNPWP);

    try {
        $statement->execute();
    } catch (PDOException $ex) {
        return false;
    }
    $statement->closeCursor();
    return true;
}

/**
 * Updates an existing storage record in the `storages` table.
 *
 * This function updates the storage code, name, address, and NPWP for a storage record
 * identified by the old storage code.
 *
 * @param string $storageCode The new code of the storage.
 * @param string $storageName The new name of the storage.
 * @param string $storageAddress The new address of the storage.
 * @param string $storageNPWP The new NPWP (tax identification number) of the storage.
 * @param string $oldCode The current code of the storage to be updated.
 *
 * @return bool|string Returns `true` if the record was successfully updated, `'duplicate'` if a duplicate entry error occurred,
 *                     `'foreign_key'` if a foreign key constraint error occurred, or `false` for any other error.
 */
function updateStorage($storageCode, $storageName, $storageAddress, $storageNPWP, $oldCode){
    global $db;

    $query = "UPDATE storages SET storageCode = :storageCode, storageName = :storageName, storageAddress = :storageAddress, storageNPWP = :storageNPWP WHERE storageCode = :oldCode";
    $statement = $db->prepare($query);
    $statement->bindValue(":storageCode", $storageCode);
    $statement->bindValue(":storageName", $storageName);
    $statement->bindValue(":storageAddress", $storageAddress);
    $statement->bindValue(":storageNPWP", $storageNPWP);
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
 * Deletes a storage record from the `storages` table.
 *
 * This function deletes a record from the `storages` table identified by the given storage code.
 *
 * @param string $storageCode The code of the storage to be deleted.
 *
 * @return bool|string Returns `true` if the record was successfully deleted, `'foreign_key'` if a foreign key constraint error occurred,
 *                     or `false` for any other error.
 */
function deleteStorage($storageCode){
    global $db;

    $query = "DELETE FROM storages WHERE storageCode = :storageCode";
    $statement = $db->prepare($query);
    $statement->bindValue(":storageCode", $storageCode);

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
 * Retrieves a storage record by its code.
 *
 * This function fetches a single storage record from the `storages` table based on the provided storage code.
 *
 * @param string $storageCode The code of the storage to retrieve.
 *
 * @return array An associative array representing the storage record, or an empty array if no record was found.
 */
function getstorageByCode($storageCode){
    global $db;

    $query = "SELECT * FROM storages WHERE storageCode = :storageCode";
    $statement = $db->prepare($query);
    $statement->bindValue(":storageCode", $storageCode);

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