<?php 

require_once "database.php";

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