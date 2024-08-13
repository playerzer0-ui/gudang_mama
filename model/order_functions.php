<?php 

require_once "database.php";

function create_slip($nomor_surat_jalan, $storageCode, $no_LPB, $no_truk, $vendorCode, $customerCode, $order_date, $purchase_order, $status) {
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
        $statement->closeCursor();
        return true;  // Return true if successful
    } catch(PDOException $ex) {
        $errorCode = $ex->getCode();
        // MySQL error code for duplicate entry
        if ($errorCode == 23000) {
            // This indicates a duplicate entry
            return false;
        } else {
            // Log the error message for debugging (optional)
            error_log($ex->getMessage());
            return false;
        }
    }
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

function generateNoLPB($storageCode, $month, $year, $status){
    global $db;

    $prefix = ($status == 1) ? "LPB" : "SJK";
    
    // Get the count of existing numbers
    $query = 'SELECT count(*) AS totalIN FROM orders WHERE month(order_date) = :mon AND year(order_date) = :yea AND status_mode = :stat AND ' . 
             ($status == 1 ? 'no_LPB' : 'nomor_surat_jalan') . ' LIKE :storageCode';
    
    $statement = $db->prepare($query);
    $statement->bindValue(":mon", $month);
    $statement->bindValue(":yea", $year);
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

    if($month < 10){
        $month = "0" . $month;
    }

    // Check for existing number and increment if necessary
    do {
        $generatedNo = $no . "/" . $prefix . "/" . $storageCode . "/" . $month . "/" . $year;
        $checkQuery = 'SELECT COUNT(*) AS existingCount FROM orders WHERE ' . 
                      ($status == 1 ? 'no_LPB' : 'nomor_surat_jalan') . ' = :generatedNo';
        
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindValue(":generatedNo", $generatedNo);
        $checkStmt->execute();
        $checkResult = $checkStmt->fetch(PDO::FETCH_ASSOC);
        $checkStmt->closeCursor();
        
        if($checkResult["existingCount"] > 0){
            $no++;
        } else {
            break;
        }
    } while(true);

    return $generatedNo;
}

function generateTaxSJ($storageCode, $month, $year){
    global $db;

    // Get the count of existing numbers
    $query = 'SELECT count(*) AS totalIN FROM orders WHERE month(order_date) = :mon AND year(order_date) = :yea AND status_mode = 3 AND nomor_surat_jalan LIKE "%SJT%" AND nomor_surat_jalan LIKE :storageCode';
    
    $statement = $db->prepare($query);
    $statement->bindValue(":mon", $month);
    $statement->bindValue(":yea", $year);
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

    if($month < 10){
        $month = "0" . $month;
    }

    // Check for existing number and increment if necessary
    do {
        $generatedNo = $no . "/SJT/" . $storageCode . "/" . $month . "/" . $year;
        $checkQuery = 'SELECT COUNT(*) AS existingCount FROM orders WHERE nomor_surat_jalan = :generatedNo';
        
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindValue(":generatedNo", $generatedNo);
        $checkStmt->execute();
        $checkResult = $checkStmt->fetch(PDO::FETCH_ASSOC);
        $checkStmt->closeCursor();
        
        if($checkResult["existingCount"] > 0){
            $no++;
        } else {
            break;
        }
    } while(true);

    return $generatedNo;
}

function updateOrderWithDependencies($nomor_surat_jalan, $storageCode, $no_LPB, $no_truk, $vendorCode, $customerCode, $order_date, $purchase_order, $old_surat_jalan) {
    global $db;

    try {
        // Begin transaction
        $db->beginTransaction();

        // Disable foreign key checks
        $db->exec('SET FOREIGN_KEY_CHECKS=0');

        // Update orders table
        $queryOrder = "UPDATE orders SET nomor_surat_jalan = :nomor_surat_jalan, storageCode = :storageCode, no_LPB = :no_LPB, no_truk = :no_truk, vendorCode = :vendorCode, customerCode = :customerCode, order_date = :order_date, purchase_order = :purchase_order WHERE nomor_surat_jalan = :old_surat_jalan";
        $statementOrder = $db->prepare($queryOrder);
        $statementOrder->bindValue(":nomor_surat_jalan", $nomor_surat_jalan);
        $statementOrder->bindValue(":storageCode", $storageCode);
        $statementOrder->bindValue(":no_LPB", $no_LPB);
        $statementOrder->bindValue(":no_truk", $no_truk);
        $statementOrder->bindValue(":vendorCode", $vendorCode);
        $statementOrder->bindValue(":customerCode", $customerCode);
        $statementOrder->bindValue(":order_date", $order_date);
        $statementOrder->bindValue(":purchase_order", $purchase_order);
        $statementOrder->bindValue(":old_surat_jalan", $old_surat_jalan);
        $statementOrder->execute();

        // Update invoices table
        $queryInvoice = "UPDATE invoices SET nomor_surat_jalan = :nomor_surat_jalan WHERE nomor_surat_jalan = :old_surat_jalan";
        $statementInvoice = $db->prepare($queryInvoice);
        $statementInvoice->bindValue(":nomor_surat_jalan", $nomor_surat_jalan);
        $statementInvoice->bindValue(":old_surat_jalan", $old_surat_jalan);
        $statementInvoice->execute();

        // Update payment table
        $queryPayment = "UPDATE payments SET nomor_surat_jalan = :nomor_surat_jalan WHERE nomor_surat_jalan = :old_surat_jalan";
        $statementPayment = $db->prepare($queryPayment);
        $statementPayment->bindValue(":nomor_surat_jalan", $nomor_surat_jalan);
        $statementPayment->bindValue(":old_surat_jalan", $old_surat_jalan);
        $statementPayment->execute();

        // Re-enable foreign key checks
        $db->exec('SET FOREIGN_KEY_CHECKS=1');

        // Commit transaction
        $db->commit();

        return true;
    } catch (PDOException $ex) {
        // Roll back transaction if any update fails
        $db->rollBack();

        // Enable foreign key checks in case of error
        $db->exec("SET FOREIGN_KEY_CHECKS=1");

        $errorCode = $ex->getCode();
        if ($errorCode == 23000) {
            $errorInfo = $ex->errorInfo;
            if (strpos($errorInfo[2], 'foreign key constraint fails') !== false) {
                return 'foreign_key';
            } elseif (strpos($errorInfo[2], 'Duplicate entry') !== false) {
                return 'duplicate';
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
                throw new Exception($ex->getMessage());
            }
        }
        return false;
    }
}

?>