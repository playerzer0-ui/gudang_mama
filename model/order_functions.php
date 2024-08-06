<?php 

require_once "database.php";

function create_slip($nomor_surat_jalan, $storageCode, $no_LPB, $no_truk, $vendorCode, $customerCode, $order_date, $purchase_order, $status){
    global $db;

    $query = 'INSERT INTO orders
        VALUES (:nomor_surat_jalan, :storageCode, :no_LPB, :no_truk, :vendorCode, :customerCode, :order_date, :purchase_order, :stat)';

    $statement = $db->prepare($query);
    $statement->bindValue(":nomor_surat_jalan", $nomor_surat_jalan);
    $statement->bindValue(":storageCode", $storageCode);
    $statement->bindValue(":no_LPB", $no_LPB);
    $statement->bindValue(":no_truk", $no_truk);
    $statement->bindValue(":vendorCode", $vendorCode);
    $statement->bindValue(":customerCode", $customerCode);
    $statement->bindValue(":order_date", $order_date);
    $statement->bindValue(":purchase_order", $purchase_order);
    $statement->bindValue(":stat", $status);

    try {
        $statement->execute();
    }
    catch(PDOException $ex){
        $ex->getMessage();
    }

    $statement->closeCursor();
}

function getAllOrders(){
    global $db;

    $query = "SELECT * FROM orders WHERE nomor_surat_jalan != '-'";
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

function getOrderByNoSJ($no_sj){
    global $db;

    $query = 'SELECT o.nomor_surat_jalan, o.storageCode, o.no_LPB, no_truk, o.vendorCode, o.customerCode, c.customerName, c.customerAddress, c.customerNPWP, o.order_date, o.purchase_order, o.status_mode FROM orders o, customers c
    WHERE o.customerCode = c.customerCode
    AND o.nomor_surat_jalan = :no_sj';
    $statement = $db->prepare($query);
    $statement->bindValue(":no_sj", $no_sj);

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

function generateNoLPB($storageCode, $status){
    global $db;

    if($status == 1){
        $query = 'SELECT count(*) AS totalIN FROM orders WHERE month(order_date) = :mon AND year(order_date) = :yea AND status_mode = :stat AND no_LPB LIKE :storageCode';
    }
    else{
        $query = 'SELECT count(*) AS totalIN FROM orders WHERE month(order_date) = :mon AND year(order_date) = :yea AND status_mode = :stat AND nomor_surat_jalan LIKE :storageCode';
    }
    $statement = $db->prepare($query);
    $statement->bindValue(":mon", date("m"));
    $statement->bindValue(":yea", date("Y"));
    $statement->bindValue(":stat", $status);
    $statement->bindValue(":storageCode", "%" . $storageCode . "%");

    try {
        $statement->execute();
    }
    catch(PDOException $ex){
        $ex->getMessage();
    }

    $result = $statement->fetch(PDO::FETCH_ASSOC);
    $no = $result["totalIN"] + 1;

    $statement->closeCursor();

    if($status == 1){
        return $no . "/LPB/" . $storageCode . "/" . date("m") . "/" . date("Y");
    }
    else{
        return $no . "/SJK/" . $storageCode . "/" . date("m") . "/" . date("Y");
    }
}

function generateTaxSJ($storageCode){
    global $db;

    $query = 'SELECT count(*) AS totalIN FROM orders WHERE month(order_date) = :mon AND year(order_date) = :yea AND status_mode = :stat AND nomor_surat_jalan LIKE "%SJT%" AND nomor_surat_jalan LIKE :storageCode';
    $statement = $db->prepare($query);
    $statement->bindValue(":mon", date("m"));
    $statement->bindValue(":yea", date("Y"));
    $statement->bindValue(":stat", 3);
    $statement->bindValue(":storageCode", "%" . $storageCode . "%");

    try {
        $statement->execute();
    }
    catch(PDOException $ex){
        $ex->getMessage();
    }

    $result = $statement->fetch(PDO::FETCH_ASSOC);
    $no = $result["totalIN"] + 1;

    $statement->closeCursor();

    return $no . "/SJT/" . $storageCode . "/" . date("m") . "/" . date("Y");
}

function updateOrder($nomor_surat_jalan, $storageCode, $no_LPB, $no_truk, $vendorCode, $customerCode, $order_date, $purchase_order){
    global $db;

    $query = "UPDATE orders SET storageCode = :storageCode, no_LPB = :no_LPB, no_truk = :no_truk, vendorCode = :vendorCode, customerCode = :customerCode, order_date = :order_date, purchase_order = :purchase_order WHERE nomor_surat_jalan = :nomor_surat_jalan";
    $statement = $db->prepare($query);
    $statement->bindValue(":storageCode", $storageCode);
    $statement->bindValue(":no_LPB", $no_LPB);
    $statement->bindValue(":no_truk", $no_truk);
    $statement->bindValue(":vendorCode", $vendorCode);
    $statement->bindValue(":customerCode", $customerCode);
    $statement->bindValue(":order_date", $order_date);
    $statement->bindValue(":purchase_order", $purchase_order);
    $statement->bindValue(":nomor_surat_jalan", $nomor_surat_jalan);

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

function deleteOrder($no_sj){
    global $db;

    $query = "DELETE FROM orders WHERE nomor_surat_jalan = :no_sj";
    $statement = $db->prepare($query);
    $statement->bindValue(":no_sj", $no_sj);

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

?>