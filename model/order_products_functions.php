<?php 

require_once "database.php";

function add_order_products($no_id, $productCode, $qty, $UOM, $price_per_UOM, $note, $status){
    global $db;

    if($status == "in" || $status == "out"){
        $query = 'INSERT INTO order_products VALUES (":no_id","-","-", ":productCode", :qty, ":UOM", :price_per_UOM, ":note", "in")';
    }
    else if($status == "repack"){
        $query = 'INSERT INTO order_products VALUES ("-","-",":no_id", ":productCode", :qty, ":UOM", :price_per_UOM, ":note", "in")';
    }
    else if($status == "moving"){
        $query = 'INSERT INTO order_products VALUES ("-",":no_id","-", ":productCode", :qty, ":UOM", :price_per_UOM, ":note", "in")';
    }

    $statement = $db->prepare($query);
    $statement->bindValue(":no_id", $no_id);
    $statement->bindValue(":productCode", $productCode);
    $statement->bindValue(":qty", $qty);
    $statement->bindValue(":UOM", $UOM);
    $statement->bindValue(":price_per_UOM", $price_per_UOM);
    $statement->bindValue(":note", $note);

    try {
        $statement->execute();
    }
    catch(PDOException $ex){
        $ex->getMessage();
    }

    $statement->closeCursor();
}

?>