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

function updateCustomer($customerCode, $customerName, $customerAddress, $customerNPWP, $oldCode){
    global $db;

    $query = "UPDATE customers SET customerCode = :customerCode, customerName = :customerName, customerAddress = :customerAddress, customerNPWP = :customerNPWP WHERE customerCode = :oldCode";
    $statement = $db->prepare($query);
    $statement->bindValue(":customerCode", $customerCode);
    $statement->bindValue(":customerName", $customerName);
    $statement->bindValue(":customerAddress", $customerAddress);
    $statement->bindValue(":customerNPWP", $customerNPWP);
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

function deleteCustomer($customerCode){
    global $db;

    $query = "DELETE FROM customers WHERE customerCode = :customerCode";
    $statement = $db->prepare($query);
    $statement->bindValue(":customerCode", $customerCode);

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