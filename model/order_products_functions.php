<?php 

require_once "database.php";
require_once "saldo_functions.php";

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

function getAllProductsForSaldo($storageCode, $month, $year){
    global $db;

    $query = 'SELECT 
        p.productCode, 
        p.productName,
        o.storageCode, 
        MONTH(i.invoice_date) AS saldoMonth, 
        YEAR(i.invoice_date) AS saldoYear, 
        SUM(op.qty) AS totalQty, 
        AVG(op.price_per_UOM) AS avgPrice,
        op.product_status
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
        AND op.product_status != "out"
    GROUP BY 
        p.productCode, 
        o.storageCode, 
        saldoMonth, 
        saldoYear,
        op.product_status

    UNION ALL

    SELECT 
        p.productCode, 
        p.productName,
        r.storageCode, 
        MONTH(r.repack_date) AS saldoMonth, 
        YEAR(r.repack_date) AS saldoYear, 
        SUM(op.qty) AS totalQty, 
        AVG(op.price_per_UOM) AS avgPrice,
        op.product_status
    FROM
        products p
    JOIN 
        order_products op ON p.productCode = op.productCode
    JOIN 
        repacks r ON op.repack_no_repack = r.no_repack
    WHERE
        r.storageCode = :storageCode1
        AND MONTH(r.repack_date) = :mon1
        AND YEAR(r.repack_date) = :yea1
    GROUP BY 
        p.productCode, 
        p.productName,
        r.storageCode, 
        saldoMonth, 
        saldoYear,
        op.product_status
    ';

    $statement = $db->prepare($query);
    $statement->bindValue(":storageCode", $storageCode);
    $statement->bindValue(":mon", $month);
    $statement->bindValue(":yea", $year);
    $statement->bindValue(":storageCode1", $storageCode);
    $statement->bindValue(":mon1", $month);
    $statement->bindValue(":yea1", $year);

    try {
        $statement->execute();
    } catch(PDOException $ex) {
        echo $ex->getMessage(); // Use echo to display the error message
    }
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);
    $statement->closeCursor();


    $movings = getAllProductsMovingSaldo($storageCode, $month, $year);

    // echo "<pre>senders" . print_r($movings, true) . "</pre>";
    // echo "<pre>inouts" . print_r($result, true) . "</pre>";

    // echo "<pre>RESULTTT" . print_r(combineForReportStock($result, $movings, $storageCode, $month, $year), true) . "</pre>";
    return [$result, $movings];


}

function getAllProductsMovingSaldo($storageCode, $month, $year){
    global $db;

    $query = 'SELECT 
            p.productCode, 
            p.productName,
            m.storageCodeSender AS storageCode, 
            MONTH(m.moving_date) AS saldoMonth, 
            YEAR(m.moving_date) AS saldoYear, 
            SUM(op.qty) AS totalQty, 
            AVG(op.price_per_UOM) AS avgPrice,
            op.product_status
        FROM
            products p
        JOIN 
            order_products op ON p.productCode = op.productCode
        JOIN 
            movings m ON op.moving_no_moving = m.no_moving
        WHERE 
            m.storageCodeSender = :storageCode
            AND MONTH(m.moving_date) = :mon
            AND YEAR(m.moving_date) = :yea
        GROUP BY 
            p.productCode, 
            p.productName,
            m.storageCodeSender, 
            saldoMonth, 
            saldoYear,
            op.product_status';

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
    $senders = $statement->fetchAll(PDO::FETCH_ASSOC);
    $statement->closeCursor();

    $query = 'SELECT 
            p.productCode, 
            p.productName,
            m.storageCodeReceiver AS storageCode, 
            MONTH(m.moving_date) AS saldoMonth, 
            YEAR(m.moving_date) AS saldoYear, 
            SUM(op.qty) AS totalQty, 
            AVG(op.price_per_UOM) AS avgPrice,
            op.product_status
        FROM
            products p
        JOIN 
            order_products op ON p.productCode = op.productCode
        JOIN 
            movings m ON op.moving_no_moving = m.no_moving
        WHERE 
            m.storageCodeReceiver = :storageCode
            AND MONTH(m.moving_date) = :mon
            AND YEAR(m.moving_date) = :yea
        GROUP BY 
            p.productCode, 
            p.productName,
            m.storageCodeReceiver, 
            saldoMonth, 
            saldoYear,
            op.product_status';

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
    $receivers = $statement->fetchAll(PDO::FETCH_ASSOC);
    $statement->closeCursor();

    return [$senders, $receivers];
}

function generateSaldo($storageCode, $month, $year){
    $storageReport = getAllProductsForSaldo($storageCode, $month, $year);
    $inouts = $storageReport[0];
    $movings = $storageReport[1];
    $date = new DateTime($year . "-" . $month . "-" . "01");
    $date->modify('-1 month');
    $prevMonth = $date->format('m');
    $prevYear = $date->format('Y');
    $data = [];

    $saldos_awal = getSaldoAwal($storageCode, $prevMonth, $prevYear);
    array_push($data, ["storageCode" => $storageCode, "month" => $month, "year" => $year]);

    foreach($inouts as $key){
        $productCode = $key["productCode"];
        if(!isset($data[$productCode])){
            $data[$productCode] = [
                "productCode" => $productCode,
                "productName" => $key["productName"],
                "saldo_awal" => ["totalQty" => 0, "price_per_qty" => 0, "totalPrice" => 0],
                "penerimaan" => [
                    "pembelian" => ["totalQty" => 0, "price_per_qty" => 0, "totalPrice" => 0],
                    "repackIn" => ["totalQty" => 0, "price_per_qty" => 0, "totalPrice" => 0],
                    "movingIn" => ["totalQty" => 0, "price_per_qty" => 0, "totalPrice" => 0],
                    "totalIn" => ["totalQty" => 0, "price_per_qty" => 0, "totalPrice" => 0]
                ],
                "pengeluaran" => [
                    "penjualan" => ["totalQty" => 0, "price_per_qty" => 0, "totalPrice" => 0],
                    "repackOut" => ["totalQty" => 0, "price_per_qty" => 0, "totalPrice" => 0],
                    "movingOut" => ["totalQty" => 0, "price_per_qty" => 0, "totalPrice" => 0],
                    "totalOut" => ["totalQty" => 0, "price_per_qty" => 0, "totalPrice" => 0]
                ],
                "barang_siap_dijual" => ["totalQty" => 0, "price_per_qty" => 0, "totalPrice" => 0],
                "saldo_akhir" => ["totalQty" => 0, "price_per_qty" => 0, "totalPrice" => 0]
            ];
        }

        if(isset($saldos_awal[$productCode])){
            $data[$productCode]["saldo_awal"]["totalQty"] = $saldos_awal[$productCode]["totalQty"];
            $data[$productCode]["saldo_awal"]["totalPrice"] = $saldos_awal[$productCode]["totalPrice"];
            $data[$productCode]["saldo_awal"]["price_per_qty"] = $saldos_awal[$productCode]["totalPrice"] / $saldos_awal[$productCode]["totalQty"];
        }

        switch($key["product_status"]){
            case "in":
                $data[$productCode]["penerimaan"]["pembelian"]["totalQty"] = $key["totalQty"];
                $data[$productCode]["penerimaan"]["pembelian"]["price_per_qty"] = $key["avgPrice"];
                $data[$productCode]["penerimaan"]["pembelian"]["totalPrice"] = $key["totalQty"] * $key["avgPrice"];

                $data[$productCode]["penerimaan"]["totalIn"]["totalQty"] += $key["totalQty"];
                $data[$productCode]["penerimaan"]["totalIn"]["totalPrice"] += $data[$productCode]["penerimaan"]["pembelian"]["totalPrice"];
                $data[$productCode]["penerimaan"]["totalIn"]["price_per_qty"] = $data[$productCode]["penerimaan"]["totalIn"]["totalPrice"] / $data[$productCode]["penerimaan"]["totalIn"]["totalQty"];
                break;

            case "out_tax":
                $data[$productCode]["pengeluaran"]["penjualan"]["totalQty"] = $key["totalQty"];
                $data[$productCode]["pengeluaran"]["penjualan"]["price_per_qty"] = $key["avgPrice"];
                $data[$productCode]["pengeluaran"]["penjualan"]["totalPrice"] = $key["totalQty"] * $key["avgPrice"];

                $data[$productCode]["pengeluaran"]["totalOut"]["totalQty"] += $key["totalQty"];
                $data[$productCode]["pengeluaran"]["totalOut"]["totalPrice"] += $data[$productCode]["pengeluaran"]["penjualan"]["totalPrice"];
                $data[$productCode]["pengeluaran"]["totalOut"]["price_per_qty"] = $data[$productCode]["pengeluaran"]["totalOut"]["totalPrice"] / $data[$productCode]["pengeluaran"]["totalOut"]["totalQty"];
                break;

            case "repack_awal":
                $data[$productCode]["pengeluaran"]["repackOut"]["totalQty"] = $key["totalQty"];
                $data[$productCode]["pengeluaran"]["repackOut"]["price_per_qty"] = $key["avgPrice"];
                $data[$productCode]["pengeluaran"]["repackOut"]["totalPrice"] = $key["totalQty"] * $key["avgPrice"];

                $data[$productCode]["pengeluaran"]["totalOut"]["totalQty"] += $key["totalQty"];
                $data[$productCode]["pengeluaran"]["totalOut"]["totalPrice"] += $data[$productCode]["pengeluaran"]["repackOut"]["totalPrice"];
                $data[$productCode]["pengeluaran"]["totalOut"]["price_per_qty"] = $data[$productCode]["pengeluaran"]["totalOut"]["totalPrice"] / $data[$productCode]["pengeluaran"]["totalOut"]["totalQty"];
                break;

            case "repack_akhir":
                $data[$productCode]["penerimaan"]["repackIn"]["totalQty"] = $key["totalQty"];
                $data[$productCode]["penerimaan"]["repackIn"]["price_per_qty"] = $key["avgPrice"];
                $data[$productCode]["penerimaan"]["repackIn"]["totalPrice"] = $key["totalQty"] * $key["avgPrice"];

                $data[$productCode]["penerimaan"]["totalIn"]["totalQty"] += $key["totalQty"];
                $data[$productCode]["penerimaan"]["totalIn"]["totalPrice"] += $data[$productCode]["penerimaan"]["repackIn"]["totalPrice"];
                $data[$productCode]["penerimaan"]["totalIn"]["price_per_qty"] = $data[$productCode]["penerimaan"]["totalIn"]["totalPrice"] / $data[$productCode]["penerimaan"]["totalIn"]["totalQty"];
                break;
        }
        
        $data[$productCode]["barang_siap_dijual"]["totalQty"] = $data[$productCode]["penerimaan"]["totalIn"]["totalQty"] + $data[$productCode]["saldo_awal"]["totalQty"];
        $data[$productCode]["barang_siap_dijual"]["totalPrice"] = $data[$productCode]["penerimaan"]["totalIn"]["totalPrice"];
        $data[$productCode]["barang_siap_dijual"]["price_per_qty"] = $data[$productCode]["barang_siap_dijual"]["totalPrice"] / $data[$productCode]["barang_siap_dijual"]["totalQty"];

        $data[$productCode]["saldo_akhir"]["totalQty"] = $data[$productCode]["barang_siap_dijual"]["totalQty"] - $data[$productCode]["pengeluaran"]["totalOut"]["totalQty"];
        $data[$productCode]["saldo_akhir"]["totalPrice"] = $data[$productCode]["barang_siap_dijual"]["totalPrice"] - $data[$productCode]["pengeluaran"]["totalOut"]["totalPrice"];
        $data[$productCode]["saldo_akhir"]["price_per_qty"] = $data[$productCode]["saldo_akhir"]["totalPrice"] / $data[$productCode]["saldo_akhir"]["totalQty"];

        updateSaldo($productCode, $storageCode, $month, $year, $data[$productCode]["saldo_akhir"]["totalQty"], $data[$productCode]["saldo_akhir"]["totalPrice"]);
    }

    return $data;
}

// function generateSaldo($storageCode, $month, $year){
//     $date = new DateTime($year . "-" . $month . "-" . "01");
//     $date->modify('-1 month');
//     $prevMonth = $date->format('m');
//     $prevYear = $date->format('Y');
    
//     echo "<pre>RESULTTT" . print_r(combineForReportStock($storageCode, $month, $year), true) . "</pre>";
// }

?>