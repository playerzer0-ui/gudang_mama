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

        case "out_tax":
            $query = 'INSERT INTO order_products VALUES (:no_id,"-","-", :productCode, :qty, :UOM, :price_per_UOM, :note, "out_tax")';
            break;

        case "repack_awal":
            $query = 'INSERT INTO order_products VALUES ("-","-",:no_id, :productCode, :qty, :UOM, :price_per_UOM, :note, "repack_awal")';
            break;
                    
        case "repack_akhir":
            $query = 'INSERT INTO order_products VALUES ("-","-",:no_id, :productCode, :qty, :UOM, :price_per_UOM, :note, "repack_akhir")';
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

function updatePriceForProductsMoving($no_id, $productCode, $price_per_UOM){
    global $db;

    $query = 'UPDATE order_products SET price_per_UOM = :price_per_UOM WHERE moving_no_moving = :no_id AND productCode = :productCode';
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

function getTotalNominalByNoSJ($no_sj){
    global $db;

    $query = "SELECT SUM(qty * price_per_UOM) AS totalNominal FROM order_products WHERE nomor_surat_jalan = :no_sj";

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

function getProductsForHutang($no_sj){
    global $db;

    $query = 'SELECT
        productCode, qty, price_per_UOM, (qty * price_per_UOM) AS nominal
    FROM order_products
    WHERE nomor_surat_jalan = :no_sj';

    $statement = $db->prepare($query);
    $statement->bindValue(':no_sj', $no_sj);

    try {
        $statement->execute();
    } catch (PDOException $ex) {
        echo $ex->getMessage();
    }

    $result = $statement->fetchAll(PDO::FETCH_ASSOC);
    $statement->closeCursor();
    return $result;
}

function getAllProductsForSaldo($storageCode, $month, $year, $product_status){
    global $db;

    switch($product_status){
        case "in":
            $query = "SELECT 
                p.productCode, 
                o.storageCode, 
                MONTH(i.invoice_date) AS saldoMonth, 
                YEAR(i.invoice_date) AS saldoYear, 
                SUM(op.qty) AS totalQty, 
                AVG(op.price_per_UOM) AS avg_price_per_qty
            FROM
                products p
            JOIN 
                order_products op ON p.productCode = op.productCode
            JOIN 
                orders o ON op.nomor_surat_jalan = o.nomor_surat_jalan
            JOIN 
                invoices i ON o.nomor_surat_jalan = i.nomor_surat_jalan
            WHERE 
                o.storageCode = :storageCode 
                AND MONTH(i.invoice_date) = :mon
                AND YEAR(i.invoice_date) = :yea
                AND op.product_status = 'in'
            GROUP BY 
                p.productCode, 
                o.storageCode, 
                invoice_month, 
                invoice_year;
            ";
            break;

        case "out_tax":
            $query = "SELECT 
                p.productCode, 
                o.storageCode, 
                MONTH(i.invoice_date) AS saldoMonth, 
                YEAR(i.invoice_date) AS saldoYear, 
                SUM(op.qty) AS totalQty, 
                AVG(op.price_per_UOM) AS avg_price_per_qty
            FROM
                products p
            JOIN 
                order_products op ON p.productCode = op.productCode
            JOIN 
                orders o ON op.nomor_surat_jalan = o.nomor_surat_jalan
            JOIN 
                invoices i ON o.nomor_surat_jalan = i.nomor_surat_jalan
            WHERE 
                o.storageCode = :storageCode 
                AND MONTH(i.invoice_date) = :mon
                AND YEAR(i.invoice_date) = :yea
                AND op.product_status = 'out_tax'
            GROUP BY 
                p.productCode, 
                o.storageCode, 
                invoice_month, 
                invoice_year;
            ";
            break;
        
        case "repack_awal":
            $query = "SELECT 
                p.productCode, 
                r.storageCode, 
                MONTH(r.repack_date) AS saldoMonth, 
                YEAR(r.repack_date) AS saldoYear, 
                SUM(op.qty) AS totalQty, 
                AVG(op.price_per_UOM) AS avg_price_per_qty
            FROM
                products p
                JOIN order_products op ON p.productCode = op.productCode
                JOIN repacks r ON op.repack_no_repack = r.no_repack
            WHERE
                r.storageCode = :storageCode
                AND MONTH(r.repack_date) = :mon
                AND YEAR(r.repack_date) = :yea
                AND op.product_status = 'repack_awal'
            GROUP BY 
                p.productCode, 
                r.storageCode, 
                repack_month, 
                repack_year;
            ";
            break;

        case "repack_akhir":
            $query = "SELECT 
                p.productCode, 
                r.storageCode, 
                MONTH(r.repack_date) AS saldoMonth, 
                YEAR(r.repack_date) AS saldoYear, 
                SUM(op.qty) AS totalQty, 
                AVG(op.price_per_UOM) AS avg_price_per_qty
            FROM
                products p
                JOIN order_products op ON p.productCode = op.productCode
                JOIN repacks r ON op.repack_no_repack = r.no_repack
            WHERE
                r.storageCode = :storageCode
                AND MONTH(r.repack_date) = :mon
                AND YEAR(r.repack_date) = :yea
                AND op.product_status = 'repack_akhir'
            GROUP BY 
                p.productCode, 
                r.storageCode, 
                repack_month, 
                repack_year;
            ";
            break;

        case "moving_sender":
            $query = "SELECT 
                p.productCode, 
                m.storageCodeSender AS storageCode, 
                MONTH(m.moving_date) AS saldoMonth, 
                YEAR(m.moving_date) AS saldoYear, 
                SUM(op.qty) AS totalQty, 
                AVG(op.price_per_UOM) AS avg_price_per_qty
            FROM
                products p
                JOIN order_products op ON p.productCode = op.productCode
                JOIN movings m ON op.moving_no_moving = m.no_moving
            WHERE
                m.storageCodeSender = :storageCode
                AND MONTH(m.moving_date) = :mon
                AND YEAR(m.moving_date) = :yea
            GROUP BY 
                p.productCode, 
                m.storageCodeSender, 
                moving_month, 
                moving_year;
            ";
            break;

        case "moving_receiver":
            $query = "SELECT 
                p.productCode, 
                m.storageCodeReceiver AS storageCode, 
                MONTH(m.moving_date) AS saldoMonth, 
                YEAR(m.moving_date) AS saldoYear, 
                SUM(op.qty) AS totalQty, 
                AVG(op.price_per_UOM) AS avg_price_per_qty
            FROM
                products p
                JOIN order_products op ON p.productCode = op.productCode
                JOIN movings m ON op.moving_no_moving = m.no_moving
            WHERE
                m.storageCodeReceiver = :storageCode
                AND MONTH(m.moving_date) = :mon
                AND YEAR(m.moving_date) = :yea
            GROUP BY 
                p.productCode, 
                m.storageCodeReceiver, 
                moving_month, 
                moving_year;
            ";
            break;
    }

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

    return $result;
}

?>