<?php

require_once "database.php";

/**
 * Retrieves all customer records from the `customers` table.
 *
 * This function fetches all records from the `customers` table.
 *
 * @return array Returns an array of associative arrays, each containing details of a customer.
 */
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

/**
 * Retrieves the column names of the `customers` table.
 *
 * This function fetches a single record from the `customers` table to determine the column names.
 *
 * @return array Returns an array of column names in the `customers` table.
 */
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

/**
 * Inserts a new customer record into the `customers` table.
 *
 * This function adds a new record to the `customers` table with the specified customer details.
 *
 * @param string $customerCode The code of the customer.
 * @param string $customerName The name of the customer.
 * @param string $customerAddress The address of the customer.
 * @param string $customerNPWP The NPWP (tax identification number) of the customer.
 *
 * @return bool Returns `true` if the insertion is successful, or `false` if an error occurs.
 */
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

/**
 * Updates an existing customer record in the `customers` table.
 *
 * This function modifies an existing customer record based on the provided new customer details.
 * It identifies the record to update by the old customer code.
 *
 * @param string $customerCode The new customer code.
 * @param string $customerName The new customer name.
 * @param string $customerAddress The new customer address.
 * @param string $customerNPWP The new NPWP of the customer.
 * @param string $oldCode The old customer code to identify the record to update.
 *
 * @return bool|string Returns `true` if the update is successful, `false` if an error occurs,
 *                     or a string ('duplicate' or 'foreign_key') if specific error types are encountered.
 */
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

/**
 * Deletes a customer record from the `customers` table.
 *
 * This function removes a record from the `customers` table based on the provided customer code.
 *
 * @param string $customerCode The customer code of the record to delete.
 *
 * @return bool|string Returns `true` if the deletion is successful, `false` if an error occurs,
 *                     or a string ('foreign_key') if a foreign key constraint violation is encountered.
 */
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

/**
 * Retrieves a customer record based on the provided customer code.
 *
 * This function fetches details of a customer record from the `customers` table using the provided customer code.
 *
 * @param string $customerCode The customer code to retrieve.
 *
 * @return array|null Returns an associative array containing the customer record details, or `null` if not found.
 */
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