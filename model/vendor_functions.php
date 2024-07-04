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