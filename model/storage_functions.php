<?php 

require_once "database.php";

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