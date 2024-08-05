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