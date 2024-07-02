<?php 

require_once "database.php";

function create_slip_in($nomor_surat_jalan, $storageCode, $no_LPB, $no_truk, $vendorCode, $order_date, $purchase_order, $status){
    global $db;

    if($status == 1){
        $query = 'INSERT INTO orders
        VALUES (:nomor_surat_jalan, :storageCode, :no_LPB, :no_truk, :vendorCode, "NON", :order_date, :purchase_order, "1")';
    }
    else{
        $query = 'INSERT INTO orders
        VALUES (:nomor_surat_jalan, :storageCode, :no_LPB, :no_truk, :vendorCode, "NON", :order_date, :purchase_order, "2")';
    }

    $statement = $db->prepare($query);
    $statement->bindValue(":nomor_surat_jalan", $nomor_surat_jalan);
    $statement->bindValue(":storageCode", $storageCode);
    $statement->bindValue(":no_LPB", $no_LPB);
    $statement->bindValue(":no_truk", $no_truk);
    $statement->bindValue(":vendorCode", $vendorCode);
    $statement->bindValue(":order_date", $order_date);
    $statement->bindValue(":purchase_order", $purchase_order);

    try {
        $statement->execute();
    }
    catch(PDOException $ex){
        $ex->getMessage();
    }

    $statement->closeCursor();
}

function generateNoLPB($storageCode, $status){
    global $db;

    $query = 'SELECT count(*) AS totalIN FROM orders WHERE month(order_date) = :mon AND year(order_date) = :yea AND status_mode = :stat';
    $statement = $db->prepare($query);
    $statement->bindValue(":mon", date("m"));
    $statement->bindValue(":yea", date("Y"));
    $statement->bindValue(":stat", $status);

    try {
        $statement->execute();
    }
    catch(PDOException $ex){
        $ex->getMessage();
    }

    $result = $statement->fetch(PDO::FETCH_ASSOC);
    $no = $result["totalIN"] + 1;

    $statement->closeCursor();

    return $no . "/LPB/" . $storageCode . "/" . date("m") . "/" . date("Y");
}

?>