<?php 

require_once "database.php";

function getAllVendors(){
    global $db;

    $query = "SELECT * FROM vendors WHERE vendorCode != 'NON'";
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