<?php 

require_once "database.php";

function getProductSuggestions($term) {
    global $db;

    $query = "SELECT productCode FROM products WHERE productCode LIKE :term LIMIT 10";
    $statement = $db->prepare($query);
    $statement->bindValue(':term', '%' . $term . '%');

    try {
        $statement->execute();
    } catch (PDOException $ex) {
        return [];
    }

    $results = $statement->fetchAll(PDO::FETCH_ASSOC);
    $statement->closeCursor();

    return array_map(function($item) {
        return $item['productCode'];
    }, $results);
}

function getAllProducts(){
    global $db;

    $query = "SELECT * FROM products";
    $statement = $db->prepare($query);

    try {
        $statement->execute();
    } catch (PDOException $ex) {
        return [];
    }

    $results = $statement->fetchAll(PDO::FETCH_ASSOC);
    $statement->closeCursor();
    return $results;
}

function getAllProductsKeyNames(){
    global $db;

    $query = "SELECT * FROM products LIMIT 1";
    $statement = $db->prepare($query);

    try {
        $statement->execute();
    } catch (PDOException $ex) {
        return [];
    }

    $results = $statement->fetchAll(PDO::FETCH_ASSOC);
    $statement->closeCursor();
    return array_keys($results[0]);
}

function createProduct($productCode, $productName){
    global $db;

    $query = "INSERT INTO products VALUES (:productCode, :productName)";
    $statement = $db->prepare($query);
    $statement->bindValue(":productCode", $productCode);
    $statement->bindValue(":productName", $productName);

    try {
        $statement->execute();
    } catch (PDOException $ex) {
        return false;
    }
    $statement->closeCursor();
    return true;
}

function updateProduct($productCode, $productName, $oldCode){
    global $db;

    $query = "UPDATE products SET productCode = :productCode, productName = :productName WHERE productCode = :oldCode";
    $statement = $db->prepare($query);
    $statement->bindValue(":productCode", $productCode);
    $statement->bindValue(":productName", $productName);
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

function deleteProduct($productCode) {
    global $db;

    $query = "DELETE FROM products WHERE productCode = :productCode";
    $statement = $db->prepare($query);
    $statement->bindValue(":productCode", $productCode);

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


function getProductByCode($productCode) {
    global $db;

    $query = "SELECT * FROM products WHERE productCode = :productCode";
    $statement = $db->prepare($query);
    $statement->bindValue(":productCode", $productCode);

    try {
        $statement->execute();
    } catch (PDOException $ex) {
        return [];
    }

    $result = $statement->fetch(PDO::FETCH_ASSOC);
    $statement->closeCursor();

    return $result;
}

?>