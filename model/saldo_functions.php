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
            if($status == "out_tax" || $status == "moving_sender" || $status == "repack_awal"){
                $query = "UPDATE saldos SET totalQty = totalQty - :qty, totalPrice = totalPrice - :price WHERE productCode = :productCode AND storageCode = :storageCode AND saldoMonth = :mon AND saldoYear = :yea";
            }
            else{
                $query = "UPDATE saldos SET totalQty = totalQty + :qty, totalPrice = totalPrice + :price WHERE productCode = :productCode AND storageCode = :storageCode AND saldoMonth = :mon AND saldoYear = :yea";
            }
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