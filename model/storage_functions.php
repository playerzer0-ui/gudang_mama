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