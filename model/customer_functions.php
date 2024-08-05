<?php

require_once "database.php";

function getAllCustomers(){
    global $db;

    $query = "SELECT * FROM customers WHERE customerCode != 'NON'";
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

function getAllCustomersKeyNames(){
    global $db;

    $query = "SELECT * FROM customers LIMIT 1";
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

function createCustomer($customerCode, $customerName, $customerAddress, $customerNPWP){
    global $db;

    $query = "INSERT INTO customers VALUES (:customerCode, :customerName, :customerAddress, :customerNPWP)";
    $statement = $db->prepare($query);
    $statement->bindValue(":customerCode", $customerCode);
    $statement->bindValue(":customerName", $customerName);
    $statement->bindValue(":customerAddress", $customerAddress);
    $statement->bindValue(":customerNPWP", $customerNPWP);

    try {
        $statement->execute();
    } catch (PDOException $ex) {
        return false;
    }
    $statement->closeCursor();
    return true;
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