<?php 

require_once "database.php";

function getAllStorages(){
    global $db;

    $query = "SELECT * FROM storages WHERE storageCode != 'NON'";
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