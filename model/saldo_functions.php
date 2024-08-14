<?php 

    require_once "database.php";

    /**
     * Checks if a saldo record exists for the given product, storage, month, and year.
     *
     * This function queries the `saldos` table to determine whether a saldo record already exists
     * for the specified product code, storage code, month, and year.
     *
     * @param string $productCode The code of the product to check.
     * @param string $storageCode The code of the storage to check.
     * @param int $month The month of the saldo record to check.
     * @param int $year The year of the saldo record to check.
     *
     * @return bool Returns `true` if the saldo record exists, otherwise `false`.
     */
    function checkExistence($productCode, $storageCode, $month, $year){
        global $db;

        $query = "SELECT * FROM saldos WHERE productCode = :productCode AND storageCode = :storageCode AND saldoMonth = :mon AND saldoYear = :yea";
        $statement = $db->prepare($query);
        $statement->bindValue(":productCode", $productCode);
        $statement->bindValue(":storageCode", $storageCode);
        $statement->bindValue(":mon", $month);
        $statement->bindValue(":yea", $year);

        try {
            $statement->execute();
        }
        catch(PDOException $ex){
            $ex->getMessage();
        }

        $rows = $statement->rowCount();
        $statement->closeCursor();
        if($rows > 0){
            return true;
        }
        else{
            return false;
        }
    }

    /**
     * Updates or inserts a saldo record for the given product, storage, month, and year.
     *
     * This function updates the `saldos` table with the specified quantity and price if a saldo record
     * exists for the given product code, storage code, month, and year. If no such record exists, it inserts
     * a new record.
     *
     * @param string $productCode The code of the product.
     * @param string $storageCode The code of the storage.
     * @param int $month The month for the saldo record.
     * @param int $year The year for the saldo record.
     * @param float $qty The quantity to be updated or inserted.
     * @param float $price The price to be updated or inserted.
     *
     * @return void This function does not return a value.
     */
    function updateSaldo($productCode, $storageCode, $month, $year, $qty, $price){
        global $db;

        $exist = checkExistence($productCode, $storageCode, $month, $year);
        if($exist){
            $query = "UPDATE saldos SET totalQty = :qty, totalPrice = :price WHERE productCode = :productCode AND storageCode = :storageCode AND saldoMonth = :mon AND saldoYear = :yea";
        }
        else{
            $query = "INSERT INTO saldos VALUES (:productCode, :storageCode, :qty, :price, :mon, :yea)";
        }

        $statement = $db->prepare($query);
        $statement->bindValue(":productCode", $productCode);
        $statement->bindValue(":storageCode", $storageCode);
        $statement->bindValue(":mon", $month);
        $statement->bindValue(":yea", $year);
        $statement->bindValue(":qty", $qty);
        $statement->bindValue(":price", $price);

        try {
            $statement->execute();
        }
        catch(PDOException $ex){
            $ex->getMessage();
        }
        $statement->closeCursor();
    }

    /**
     * Retrieves the initial saldo for a given storage, month, and year.
     *
     * This function fetches all saldo records for the specified storage code, month, and year
     * and returns them as an associative array indexed by product code.
     *
     * @param string $storageCode The code of the storage to retrieve saldos for.
     * @param int $month The month of the saldo records to retrieve.
     * @param int $year The year of the saldo records to retrieve.
     *
     * @return array An associative array where the key is the product code and the value is an array
     *               containing the storage code, total quantity, and total price for that product.
     */
    function getSaldoAwal($storageCode, $month, $year){
        global $db;
        $data = [];

        $query = "SELECT * FROM saldos WHERE storageCode = :storageCode AND saldoMonth = :mon AND saldoYear = :yea";
        $statement = $db->prepare($query);
        $statement->bindValue(":storageCode", $storageCode);
        $statement->bindValue(":mon", $month);
        $statement->bindValue(":yea", $year);

        try {
            $statement->execute();
        }
        catch(PDOException $ex){
            $ex->getMessage();
        }

        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        $statement->closeCursor();

        foreach($result as $key){
            $productCode = $key["productCode"];
            $data[$productCode] = [
                "storageCode" => $key["storageCode"],
                "totalQty" => $key["totalQty"],
                "totalPrice" => $key["totalPrice"]
            ];
        }

        return $data;
    }

?>