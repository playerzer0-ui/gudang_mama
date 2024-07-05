<?php

require_once "database.php";

function getAllCustomers(){
    global $db;

    $query = "SELECT * FROM customers";
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

function getCustomerByCode($customerCode){
    global $db;

    $query = "SELECT * FROM customers WHERE customerCode = :customerCode";
    $statement = $db->prepare($query);
    $statement->bindValue(":customerCode", $customerCode);

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