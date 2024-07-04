<?php 

require_once "database.php";

function addOrderProducts($no_id, $productCode, $qty, $UOM, $note, $status){
    global $db;

    switch($status){
        case "in":
            $query = 'INSERT INTO order_products VALUES (:no_id,"-","-", :productCode, :qty, :UOM, :price_per_UOM, :note, "in")';
            break;

        case "out":
            $query = 'INSERT INTO order_products VALUES (:no_id,"-","-", :productCode, :qty, :UOM, :price_per_UOM, :note, "out")';
            break;

        case "repack awal":
            $query = 'INSERT INTO order_products VALUES ("-","-",:no_id, :productCode, :qty, :UOM, :price_per_UOM, :note, "repack awal")';
            break;
                    
        case "repack akhir":
            $query = 'INSERT INTO order_products VALUES ("-","-",:no_id, :productCode, :qty, :UOM, :price_per_UOM, :note, "repack akhir")';
            break;  
        
        case "moving":
            $query = 'INSERT INTO order_products VALUES ("-",:no_id,"-", :productCode, :qty, :UOM, :price_per_UOM, :note, "moving")';
            break; 
    }

    $statement = $db->prepare($query);
    $statement->bindValue(":no_id", $no_id);
    $statement->bindValue(":productCode", $productCode);
    $statement->bindValue(":qty", $qty);
    $statement->bindValue(":UOM", $UOM);
    $statement->bindValue(":price_per_UOM", 0);
    $statement->bindValue(":note", $note);

    try {
        $statement->execute();
    }
    catch(PDOException $ex){
        $ex->getMessage();
    }

    $statement->closeCursor();
}

function getOrderProductsFromNoID($no_id, $status){
    global $db;

    switch($status){
        case "in":
            $query = 'SELECT op.nomor_surat_jalan, op.productCode, p.productName, op.qty, op.uom, op.price_per_UOM FROM order_products op, products p WHERE op.nomor_surat_jalan = :no_id AND op.productCode = p.productCode';
            break;

        case "out":
            $query = 'SELECT op.nomor_surat_jalan, op.productCode, p.productName, op.qty, op.uom, op.price_per_UOM FROM order_products op, products p WHERE op.nomor_surat_jalan = :no_id AND op.productCode = p.productCode';
            break;

        case "repack":
            $query = 'SELECT op.repack_no_repack, op.productCode, p.productName, op.qty, op.uom, op.price_per_UOM FROM order_products op, products p WHERE op.repack_no_repack = :no_id AND op.productCode = p.productCode';
            break;
        
        case "moving":
            $query = 'SELECT op.repack_no_repack, op.productCode, p.productName, op.qty, op.uom, op.price_per_UOM FROM order_products op, products p WHERE op.moving_no_moving = :no_id AND op.productCode = p.productCode';
            break; 
    }
    $statement = $db->prepare($query);
    $statement->bindValue(":no_id", $no_id);

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

function updatePriceForProducts($no_id, $productCode, $price_per_UOM){
    global $db;

    $query = 'UPDATE order_products SET price_per_UOM = :price_per_UOM WHERE nomor_surat_jalan = :no_id AND productCode = :productCode';
    $statement = $db->prepare($query);
    $statement->bindValue(":price_per_UOM", $price_per_UOM);
    $statement->bindValue(":no_id", $no_id);
    $statement->bindValue(":productCode", $productCode);

    try {
        $statement->execute();
    }
    catch(PDOException $ex){
        $ex->getMessage();
    }

    $statement->closeCursor();
}

?>