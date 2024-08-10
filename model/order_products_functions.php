<?php 

require_once "database.php";
require_once "saldo_functions.php";

// Declare a global variable
$global_repackOut_price_per_qty = 0;
// $data = [];
// $productCode = "";

function addOrderProducts($no_id, $productCode, $qty, $UOM, $price_per_UOM, $note, $status){
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

/**
 * delete order products on order_products
 * @param string $no_id is the code for either order, repack or moving
 * @param string $status can be 3 things, order, repack or moving
 */
function deleteOrderProducts($no_id, $status){
    global $db;

    switch($status){
        case "order":
            $query = 'DELETE FROM order_products WHERE nomor_surat_jalan = :no_id';
            break;

        case "repack":
            $query = 'DELETE FROM order_products WHERE repack_no_repack = :no_id';
            break;
        
        case "moving":
            $query = 'DELETE FROM order_products WHERE moving_no_moving = :no_id';
            break; 
    }
    $statement = $db->prepare($query);
    $statement->bindValue(":no_id", $no_id);

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
 * delete order products on order_products
 * @param string $no_id is the code for either in(order), repack or moving
 * @param string $status can be 3 things, in(order), repack or moving
 */
function getOrderProductsFromNoID($no_id, $status){
    global $db;

    switch($status){
        case "in":
            $query = 'SELECT op.nomor_surat_jalan, op.productCode, p.productName, op.qty, op.uom, op.price_per_UOM, op.note, op.product_status FROM order_products op, products p WHERE op.nomor_surat_jalan = :no_id AND op.productCode = p.productCode';
            break;

        case "repack":
            $query = 'SELECT op.repack_no_repack, op.productCode, p.productName, op.qty, op.uom, op.price_per_UOM, op.note, op.product_status FROM order_products op, products p WHERE op.repack_no_repack = :no_id AND op.productCode = p.productCode';
            break;
        
        case "moving":
            $query = 'SELECT op.moving_no_moving, op.productCode, p.productName, op.qty, op.uom, op.price_per_UOM, op.note, op.product_status FROM order_products op, products p WHERE op.moving_no_moving = :no_id AND op.productCode = p.productCode';
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

    if(!strpos($no_sj, "SJP")){
        $query = "SELECT SUM(qty * price_per_UOM) AS totalNominal FROM order_products WHERE nomor_surat_jalan = :no_sj";
    }
    else{
        $query = "SELECT SUM(qty * price_per_UOM) AS totalNominal FROM order_products WHERE moving_no_moving = :no_sj";
    }

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

    $query = '(SELECT 
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
            p.productName,
            o.storageCode, 
            saldoMonth, 
            saldoYear,
            op.product_status
    )
    UNION ALL
    (
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
    )
    ORDER BY
        CASE 
            WHEN product_status = "in" THEN 1
            WHEN product_status = "out_tax" THEN 2
            WHEN product_status = "repack_awal" THEN 3
            WHEN product_status = "repack_akhir" THEN 4
            ELSE 5
        END';

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

/**
 * moving saldo function
 * @return array senders and receivers in that order
 */
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

function generateSaldo($storageCode, $month, $year) {
    global $global_repackOut_price_per_qty;

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

    foreach ($inouts as $key) {
        $productCode = $key["productCode"];
        if (!isset($data[$productCode])) {
            $data[$productCode] = initializeProductData($key["productCode"], $key["productName"]);
        }

        updateSaldoAwal($data[$productCode], $saldos_awal);

        switch ($key["product_status"]) {
            case "in":
                updatePenerimaan($data[$productCode], $key, "pembelian");
                break;

            case "out_tax":
                updatePengeluaran($data[$productCode], $key, "penjualan");
                break;

            case "repack_awal":
                updatePengeluaran($data[$productCode], $key, "repackOut");
                // $global_repackOut_price_per_qty = $data[$productCode]["pengeluaran"]["repackOut"]["price_per_qty"];
                break;

            case "repack_akhir":
                updatePenerimaan($data[$productCode], $key, "repackIn");
                // $data[$productCode]["penerimaan"]["repackIn"]["price_per_qty"] = $global_repackOut_price_per_qty;
                // $data[$productCode]["penerimaan"]["repackIn"]["totalPrice"] = $key["totalQty"] * $global_repackOut_price_per_qty;
                break;
        }

        updateBarangSiapDijual($data[$productCode]);
        updateSaldoAkhir($data[$productCode]);

        updateSaldo($productCode, $storageCode, $month, $year, $data[$productCode]["saldo_akhir"]["totalQty"], $data[$productCode]["saldo_akhir"]["totalPrice"]);
    }

    foreach ($movings[1] as $key) {
        $productCode = $key["productCode"];
        if (!isset($data[$productCode])) {
            $data[$productCode] = initializeProductData($key["productCode"], $key["productName"]);
        }

        updateSaldoAwal($data[$productCode], $saldos_awal);

        // Handle movingIn logic here
        updatePenerimaan($data[$productCode], $key, "movingIn");

        updateBarangSiapDijual($data[$productCode]);
        updateSaldoAkhir($data[$productCode]);

        updateSaldo($productCode, $storageCode, $month, $year, $data[$productCode]["saldo_akhir"]["totalQty"], $data[$productCode]["saldo_akhir"]["totalPrice"]);
    }

    foreach ($movings[0] as $key) {
        $productCode = $key["productCode"];
        if (!isset($data[$productCode])) {
            $data[$productCode] = initializeProductData($key["productCode"], $key["productName"]);
        }

        updateSaldoAwal($data[$productCode], $saldos_awal);

        // Handle movingOut logic here
        updatePengeluaran($data[$productCode], $key, "movingOut");

        updateBarangSiapDijual($data[$productCode]);
        updateSaldoAkhir($data[$productCode]);

        updateSaldo($productCode, $storageCode, $month, $year, $data[$productCode]["saldo_akhir"]["totalQty"], $data[$productCode]["saldo_akhir"]["totalPrice"]);
    }

    return $data;
}

function initializeProductData($productCode, $productName) {
    return [
        "productCode" => $productCode,
        "productName" => $productName,
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

function updateSaldoAwal(&$productData, $saldos_awal) {
    $productCode = $productData["productCode"];
    if (isset($saldos_awal[$productCode])) {
        $productData["saldo_awal"]["totalQty"] = $saldos_awal[$productCode]["totalQty"];
        $productData["saldo_awal"]["totalPrice"] = $saldos_awal[$productCode]["totalPrice"];
        
        if ($saldos_awal[$productCode]["totalQty"] > 0) {
            $productData["saldo_awal"]["price_per_qty"] = $saldos_awal[$productCode]["totalPrice"] / $saldos_awal[$productCode]["totalQty"];
        } else {
            $productData["saldo_awal"]["price_per_qty"] = 0; // or handle this case as needed
        }
    }
}


function updatePenerimaan(&$productData, $key, $type) {
    global $global_repackOut_price_per_qty;

    $productData["penerimaan"][$type]["totalQty"] = $key["totalQty"];
    $productData["penerimaan"][$type]["price_per_qty"] = $key["avgPrice"];
    $productData["penerimaan"][$type]["totalPrice"] = $key["totalQty"] * $key["avgPrice"];

    if ($type == "repackIn") {
        $productData["penerimaan"][$type]["price_per_qty"] = $global_repackOut_price_per_qty;
        $productData["penerimaan"][$type]["totalPrice"] = $key["totalQty"] * $global_repackOut_price_per_qty;
    }

    $productData["penerimaan"]["totalIn"]["totalQty"] += $key["totalQty"];
    $productData["penerimaan"]["totalIn"]["totalPrice"] += $productData["penerimaan"][$type]["totalPrice"];
    
    if ($productData["penerimaan"]["totalIn"]["totalQty"] > 0) {
        $productData["penerimaan"]["totalIn"]["price_per_qty"] = $productData["penerimaan"]["totalIn"]["totalPrice"] / $productData["penerimaan"]["totalIn"]["totalQty"];
    } else {
        $productData["penerimaan"]["totalIn"]["price_per_qty"] = 0;
    }
}


function updatePengeluaran(&$productData, $key, $type) {
    global $global_repackOut_price_per_qty;

    $price_per_qty = $productData["barang_siap_dijual"]["price_per_qty"];

    $productData["pengeluaran"][$type]["totalQty"] = $key["totalQty"];
    $productData["pengeluaran"][$type]["price_per_qty"] = $price_per_qty;
    $productData["pengeluaran"][$type]["totalPrice"] = $key["totalQty"] * $price_per_qty;

    $global_repackOut_price_per_qty = $productData["pengeluaran"][$type]["price_per_qty"];

    $productData["pengeluaran"]["totalOut"]["totalQty"] += $key["totalQty"];
    $productData["pengeluaran"]["totalOut"]["totalPrice"] += $productData["pengeluaran"][$type]["totalPrice"];
    
    if ($productData["pengeluaran"]["totalOut"]["totalQty"] > 0) {
        $productData["pengeluaran"]["totalOut"]["price_per_qty"] = $productData["pengeluaran"]["totalOut"]["totalPrice"] / $productData["pengeluaran"]["totalOut"]["totalQty"];
    } else {
        $productData["pengeluaran"]["totalOut"]["price_per_qty"] = 0;
    }
}

function updateBarangSiapDijual(&$productData) {
    $productData["barang_siap_dijual"]["totalQty"] = $productData["penerimaan"]["totalIn"]["totalQty"] + $productData["saldo_awal"]["totalQty"];
    $productData["barang_siap_dijual"]["totalPrice"] = $productData["penerimaan"]["totalIn"]["totalPrice"] + $productData["saldo_awal"]["totalPrice"];
    
    if ($productData["barang_siap_dijual"]["totalQty"] > 0) {
        $productData["barang_siap_dijual"]["price_per_qty"] = $productData["barang_siap_dijual"]["totalPrice"] / $productData["barang_siap_dijual"]["totalQty"];
    } else {
        $productData["barang_siap_dijual"]["price_per_qty"] = 0;
    }
}

function updateSaldoAkhir(&$productData) {
    $productData["saldo_akhir"]["totalQty"] = $productData["barang_siap_dijual"]["totalQty"] - $productData["pengeluaran"]["totalOut"]["totalQty"];
    $productData["saldo_akhir"]["totalPrice"] = $productData["barang_siap_dijual"]["totalPrice"] - $productData["pengeluaran"]["totalOut"]["totalPrice"];
    
    if ($productData["saldo_akhir"]["totalQty"] > 0) {
        $productData["saldo_akhir"]["price_per_qty"] = $productData["saldo_akhir"]["totalPrice"] / $productData["saldo_akhir"]["totalQty"];
    } else {
        $productData["saldo_akhir"]["price_per_qty"] = 0;
    }
}

?>