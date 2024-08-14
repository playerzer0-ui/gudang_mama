<?php 

require_once "database.php";

/**
 * Retrieves product codes that match a given search term.
 *
 * This function searches for product codes that contain the provided term, returning a maximum of 10 results.
 *
 * @param string $term The search term to find matching product codes.
 *
 * @return array Returns an array of product codes that match the search term.
 */
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

/**
 * Retrieves product codes that match a given search term.
 *
 * This function searches for product codes that contain the provided term, returning a maximum of 10 results.
 *
 * @param string $term The search term to find matching product codes.
 *
 * @return array Returns an array of product codes that match the search term.
 */
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

/**
 * Retrieves the column names of the `products` table.
 *
 * This function fetches one record from the `products` table and returns the column names.
 *
 * @return array Returns an array of column names for the `products` table.
 */
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

/**
 * Inserts a new product record into the `products` table.
 *
 * This function adds a new product record with the specified product code and name to the `products` table.
 *
 * @param string $productCode The code of the new product.
 * @param string $productName The name of the new product.
 *
 * @return bool Returns `true` if the product is successfully created, `false` otherwise.
 */
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

/**
 * Updates an existing product record in the `products` table.
 *
 * This function modifies an existing product record based on the provided new product details.
 * It identifies the record to update by the old product code.
 *
 * @param string $productCode The new product code.
 * @param string $productName The new product name.
 * @param string $oldCode The current product code of the record to update.
 *
 * @return bool|string Returns `true` if the update is successful, `false` if an error occurs,
 *                     or a string ('duplicate' or 'foreign_key') if a constraint error is encountered.
 */
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

/**
 * Deletes a specific product record from the `products` table.
 *
 * This function removes a product record from the `products` table based on the provided product code.
 *
 * @param string $productCode The code of the product record to delete.
 *
 * @return bool|string Returns `true` if the deletion is successful, `false` if an error occurs,
 *                     or a string ('foreign_key') if a foreign key constraint violation is encountered.
 */
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

/**
 * Retrieves a specific product record based on the provided product code.
 *
 * This function fetches details of a product record from the `products` table using the provided product code.
 *
 * @param string $productCode The code of the product to retrieve.
 *
 * @return array|null Returns an associative array containing the product record details, or `null` if not found.
 */
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