<?php 

    require_once "database.php";

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

    function updateSaldo($productCode, $storageCode, $month, $year, $qty, $price, $status){
        global $db;

        $exist = checkExistence($productCode, $storageCode, $month, $year);
        if($exist){
            $query = "UPDATE saldos SET totalQty = totalQty + :qty, price_per_qty = price_per_qty + :price, saldoCount = saldoCount + 1 WHERE productCode = :productCode AND storageCode = :storageCode AND saldoMonth = :mon AND saldoYear = :yea";
        }
        else{
            $query = "INSERT INTO saldos VALUES (:productCode, :storageCode, :qty, :price, :mon, :yea, 1)";
        }

        if($status == "out_tax" || $status == "moving_sender" || $status == "repack_awal"){
            $qty *= -1;
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

?>