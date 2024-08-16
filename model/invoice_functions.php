<?php

    require_once "database.php";
    require_once "../model/order_products_functions.php";

    /**
     * Creates a new invoice record in the `invoices` table.
     *
     * This function inserts a new invoice record with the provided details into the `invoices` table.
     *
     * @param string $nomor_surat_jalan The order number (nomor_surat_jalan) associated with the invoice.
     * @param string $invoice_date The date of the invoice.
     * @param string $no_invoice The invoice number.
     * @param string $no_faktur The invoice reference number (no_faktur).
     * @param string $no_moving The moving number associated with the invoice.
     * @param float $tax The tax amount for the invoice.
     *
     * @return void This function does not return a value.
     * 
     * @throws PDOException Throws an exception if there is an error executing the SQL query.
     */
    function create_invoice($nomor_surat_jalan, $invoice_date, $no_invoice, $no_faktur, $no_moving, $tax){
        global $db;
    
        $query = 'INSERT INTO invoices
            VALUES (:nomor_surat_jalan, :invoice_date, :no_invoice, :no_faktur, :no_moving, :tax)';
    
        $statement = $db->prepare($query);
        $statement->bindValue(":nomor_surat_jalan", $nomor_surat_jalan);
        $statement->bindValue(":invoice_date", $invoice_date);
        $statement->bindValue(":no_invoice", $no_invoice);
        $statement->bindValue(":no_faktur", $no_faktur);
        $statement->bindValue(":no_moving", $no_moving);
        $statement->bindValue(":tax", $tax);
    
        try {
            $statement->execute();
        }
        catch(PDOException $ex){
            $ex->getMessage();
        }
    
        $statement->closeCursor();
    }

    /**
     * Retrieves all invoices from the `invoices` table.
     *
     * This function fetches all records from the `invoices` table.
     *
     * @return array Returns an associative array of all invoice records. Each record is an associative array with column names as keys.
     * 
     * @throws PDOException Throws an exception if there is an error executing the SQL query.
     */
    function getAllInvoices(){
        global $db;

        $query = "SELECT * FROM invoices";
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

    /**
     * Generates a new invoice number based on the given storage code, month, and year.
     *
     * This function generates a new invoice number that does not conflict with existing invoice numbers for the specified month, year, and storage code.
     *
     * @param string $storageCode The storage code used to generate the invoice number.
     * @param int $month The month for the invoice number.
     * @param int $year The year for the invoice number.
     *
     * @return string Returns the generated invoice number.
     * 
     * @throws PDOException Throws an exception if there is an error executing the SQL query.
     */
    function generateNoInvoice($storageCode, $month, $year){
        global $db;
    
        // Retrieve all existing invoice numbers for the specified month, year, and storage code
        $query = 'SELECT no_invoice FROM invoices 
                  WHERE month(invoice_date) = :mon AND year(invoice_date) = :yea AND no_invoice LIKE :storageCode
                  ORDER BY no_invoice';
    
        $statement = $db->prepare($query);
        $statement->bindValue(":mon", $month);
        $statement->bindValue(":yea", $year);
        $statement->bindValue(":storageCode", "%" . $storageCode . "%");
    
        try {
            $statement->execute();
        } catch (PDOException $ex) {
            $ex->getMessage();
        }
    
        $existingInvoices = $statement->fetchAll(PDO::FETCH_COLUMN);
        $statement->closeCursor();
    
        // Initialize the number
        $no = 1;
        $invoiceNumbers = array_map(function($invoice) {
            // Extract the numeric part of the invoice number
            return (int) explode('/', $invoice)[0];
        }, $existingInvoices);
    
        // Find the smallest available number
        while (in_array($no, $invoiceNumbers)) {
            $no++;
        }
    
        if ($month < 10) {
            $month = "0" . $month;
        }
    
        // Return the new invoice number
        return $no . "/INV/" . $storageCode . "/" . $month . "/" . $year;
    }    

    /**
     * Checks if an invoice with the specified invoice number exists.
     *
     * This function queries the `invoices` table to determine if a record with the given invoice number exists.
     *
     * @param string $invoiceNo The invoice number to check for existence.
     *
     * @return bool Returns true if the invoice exists, otherwise false.
     * 
     * @throws PDOException Throws an exception if there is an error executing the SQL query.
     */
    function invoiceExists($invoiceNo) {
        global $db;
    
        $query = 'SELECT COUNT(*) FROM invoices WHERE no_invoice = :invoiceNo';
    
        $statement = $db->prepare($query);
        $statement->bindValue(':invoiceNo', $invoiceNo);
    
        try {
            $statement->execute();
        } catch (PDOException $ex) {
            $ex->getMessage();
        }
    
        $exists = $statement->fetchColumn() > 0;
    
        $statement->closeCursor();
    
        return $exists;
    }

    /**
     * Checks if an invoice with the specified order number (nomor_surat_jalan) exists.
     *
     * This function queries the `invoices` table to determine if a record with the given order number exists.
     *
     * @param string $no_sj The order number (nomor_surat_jalan) to check for existence.
     *
     * @return bool Returns true if the invoice exists, otherwise false.
     * 
     * @throws PDOException Throws an exception if there is an error executing the SQL query.
     */
    function invoiceSJExists($no_sj) {
        global $db;
    
        $query = 'SELECT COUNT(*) FROM invoices WHERE nomor_surat_jalan = :no_sj';
    
        $statement = $db->prepare($query);
        $statement->bindValue(':no_sj', $no_sj);
    
        try {
            $statement->execute();
        } catch (PDOException $ex) {
            $ex->getMessage();
        }
    
        $exists = $statement->fetchColumn() > 0;
    
        $statement->closeCursor();
    
        return $exists;
    }

    /**
     * Checks if an invoice with the specified moving number exists.
     *
     * This function queries the `invoices` table to determine if a record with the given moving number exists.
     *
     * @param string $invoiceNo The moving number to check for existence.
     *
     * @return bool Returns true if the invoice exists, otherwise false.
     * 
     * @throws PDOException Throws an exception if there is an error executing the SQL query.
     */
    function invoiceMovingExists($invoiceNo) {
        global $db;
    
        $query = 'SELECT COUNT(*) FROM invoices WHERE no_moving = :invoiceNo';
    
        $statement = $db->prepare($query);
        $statement->bindValue(':invoiceNo', $invoiceNo);
    
        try {
            $statement->execute();
        } catch (PDOException $ex) {
            $ex->getMessage();
        }
    
        $exists = $statement->fetchColumn() > 0;
    
        $statement->closeCursor();
    
        return $exists;
    }

    /**
     * Retrieves an invoice based on the order number (nomor_surat_jalan) or moving number.
     *
     * This function queries the `invoices` table and returns the invoice record that matches either the given order number
     * or moving number, depending on which is provided.
     *
     * @param string $nomor_surat_jalan The order number (nomor_surat_jalan) to search for (can be null).
     * @param string|null $no_moving The moving number to search for (can be null).
     *
     * @return array|null Returns an associative array of the invoice record if found, otherwise null.
     * 
     * @throws PDOException Throws an exception if there is an error executing the SQL query.
     */
    function getInvoiceByNoSJ($nomor_surat_jalan, $no_moving){
        global $db;

        if($no_moving == null){
            $query = "SELECT * FROM invoices WHERE nomor_surat_jalan = :nomor_surat_jalan";
            $statement = $db->prepare($query);
            $statement->bindValue(":nomor_surat_jalan", $nomor_surat_jalan);
        }
        else{
            $query = "SELECT * FROM invoices WHERE no_moving = :no_moving";
            $statement = $db->prepare($query);
            $statement->bindValue(":no_moving", $no_moving);
        }

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

    /**
     * Retrieves details of outstanding debts or receivables based on the specified mode.
     *
     * This function queries the database to get information about invoices, payments, and associated entities (vendors or customers)
     * for a given month, year, storage code, and mode. The mode determines whether the details are for debts ("hutang") or receivables.
     *
     * @param int $month The month to filter invoices by.
     * @param int $year The year to filter invoices by.
     * @param string $storageCode The storage code to filter invoices by.
     * @param string $mode The mode to determine if the details are for "hutang" (debts) or "piutang" (receivables).
     *
     * @return array An array of associative arrays where each associative array contains details about an invoice and associated payments.
     *
     * @throws PDOException Throws an exception if there is an error executing the SQL query.
     */
    function getHutangDetails($month, $year, $storageCode, $mode){
        global $db;

        if($mode == "hutang"){
            $query = 'SELECT 
                o.nomor_surat_jalan,
                i.invoice_date, 
                i.no_invoice,
                i.tax,
                v.vendorName, 
                COALESCE(p.payment_date, "-") AS payment_date, 
                COALESCE(p.payment_amount, 0) AS payment_amount
            FROM 
                orders o
            JOIN 
                invoices i ON o.nomor_surat_jalan = i.nomor_surat_jalan
            JOIN 
                vendors v ON o.vendorCode = v.vendorCode
            LEFT JOIN 
                payments p ON o.nomor_surat_jalan = p.nomor_surat_jalan
            WHERE 
                MONTH(i.invoice_date) = :mon
                AND YEAR(i.invoice_date) = :yea
                AND o.storageCode = :storageCode
                AND o.status_mode = 1';
        }
        else{
            $query = 'SELECT 
                o.nomor_surat_jalan,
                i.invoice_date, 
                i.no_invoice, 
                i.tax,
                c.customerName, 
                COALESCE(p.payment_date, "-") AS payment_date, 
                COALESCE(p.payment_amount, 0) AS payment_amount
            FROM 
                orders o
            JOIN 
                invoices i ON o.nomor_surat_jalan = i.nomor_surat_jalan
            JOIN 
                customers c ON o.customerCode = c.customerCode
            LEFT JOIN 
                payments p ON o.nomor_surat_jalan = p.nomor_surat_jalan
            WHERE 
                MONTH(i.invoice_date) = :mon
                AND YEAR(i.invoice_date) = :yea
                AND o.storageCode = :storageCode
                AND o.status_mode = 2';
        }

        $statement = $db->prepare($query);
        $statement->bindValue(':mon', $month);
        $statement->bindValue(':yea', $year);
        $statement->bindValue(':storageCode', $storageCode);

        try {
            $statement->execute();
        } catch (PDOException $ex) {
            echo $ex->getMessage();
        }

        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        $statement->closeCursor();
        return $result;
    }

    /**
     * Generates a report of outstanding debts or receivables, including associated products and payments.
     *
     * This function calls `getHutangDetails` to retrieve invoice details based on the specified month, year, storage code, and mode.
     * It then groups the data by invoice number and includes details about associated products and payments.
     *
     * @param int $month The month to filter invoices by.
     * @param int $year The year to filter invoices by.
     * @param string $storageCode The storage code to filter invoices by.
     * @param string $mode The mode to determine if the report is for "hutang" (debts) or "piutang" (receivables).
     *
     * @return array An array of associative arrays where each associative array represents a grouped report with invoice details,
     *               associated products, and payments.
     *
     * @throws Exception Throws an exception if there is an error fetching products for an invoice.
     */
    function getLaporanHutangPiutang($month, $year, $storageCode, $mode) {
        $groupData = [];
        $hutangDetails = getHutangDetails($month, $year, $storageCode, $mode);
        
        if($mode == "hutang"){
            foreach($hutangDetails as $details){
                $hutangKey = $details["nomor_surat_jalan"];
                if(!isset($groupData[$hutangKey])){
                    $groupData[$hutangKey] = [
                        "invoice_date" => $details["invoice_date"],
                        "no_invoice" => $details["no_invoice"],
                        "tax" => $details["tax"],
                        "vendorName" => $details["vendorName"],
                        "payments" => [],
                        "products" => []
                    ];
    
                    $productsList = getProductsForHutang($hutangKey);
                    foreach($productsList as $key){
                        array_push($groupData[$hutangKey]["products"], [
                            "productCode" => $key["productCode"],
                            "qty" => $key["qty"],
                            "price_per_UOM" => $key["price_per_UOM"],
                            "nominal" => $key["nominal"]
                        ]);
                    }
                }
    
                array_push($groupData[$hutangKey]["payments"], [
                    "payment_date" => $details["payment_date"],
                    "payment_amount" => $details["payment_amount"]
                ]);
            }
        }
        else{
            foreach($hutangDetails as $details){
                $hutangKey = $details["nomor_surat_jalan"];
                if(!isset($groupData[$hutangKey])){
                    $groupData[$hutangKey] = [
                        "invoice_date" => $details["invoice_date"],
                        "no_invoice" => $details["no_invoice"],
                        "tax" => $details["tax"],
                        "customerName" => $details["customerName"],
                        "payments" => [],
                        "products" => []
                    ];
    
                    $productsList = getProductsForHutang($hutangKey);
                    foreach($productsList as $key){
                        array_push($groupData[$hutangKey]["products"], [
                            "productCode" => $key["productCode"],
                            "qty" => $key["qty"],
                            "price_per_UOM" => $key["price_per_UOM"],
                            "nominal" => $key["nominal"]
                        ]);
                    }
                }
    
                array_push($groupData[$hutangKey]["payments"], [
                    "payment_date" => $details["payment_date"],
                    "payment_amount" => $details["payment_amount"]
                ]);
            }
        }

        return array_values($groupData);
    }

    /**
     * Updates an invoice record based on either the surat jalan or moving number.
     *
     * This function updates the `invoices` table with new values for `invoice_date`, `no_invoice`, `no_faktur`, and `tax`. 
     * The update is determined by whether `no_moving` is provided or not. 
     * If `no_moving` is "-", the function updates based on `nomor_surat_jalan`. Otherwise, it updates based on `no_moving`.
     *
     * @param string $nomor_surat_jalan The surat jalan number for the invoice.
     * @param string $invoice_date The date of the invoice.
     * @param string $no_invoice The invoice number.
     * @param string $no_faktur The faktur number.
     * @param string $no_moving The moving number (if applicable). Pass "-" if not applicable.
     * @param float $tax The tax amount for the invoice.
     *
     * @return bool Returns `true` if the update is successful, or `false` if an error occurs.
     * 
     * @throws string Returns 'foreign_key' if a foreign key constraint violation occurs.
     */
    function updateInvoice($nomor_surat_jalan, $invoice_date, $no_invoice, $no_faktur, $no_moving, $tax){
        global $db;
    
        if($no_moving == "-"){
            $query = "UPDATE invoices SET invoice_date = :invoice_date, no_invoice = :no_invoice, no_faktur = :no_faktur, tax = :tax WHERE nomor_surat_jalan = :nomor_surat_jalan";
            $statement = $db->prepare($query);
            $statement->bindValue(":invoice_date", $invoice_date);
            $statement->bindValue(":no_invoice", $no_invoice);
            $statement->bindValue(":no_faktur", $no_faktur);
            $statement->bindValue(":tax", $tax);
            $statement->bindValue(":nomor_surat_jalan", $nomor_surat_jalan);
        }
        else{
            $query = "UPDATE invoices SET invoice_date = :invoice_date, no_invoice = :no_invoice, no_faktur = :no_faktur, tax = :tax WHERE no_moving = :no_moving";
            $statement = $db->prepare($query);
            $statement->bindValue(":invoice_date", $invoice_date);
            $statement->bindValue(":no_invoice", $no_invoice);
            $statement->bindValue(":no_faktur", $no_faktur);
            $statement->bindValue(":tax", $tax);
            $statement->bindValue(":no_moving", $no_moving);
        }
    
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
     * Deletes an invoice record based on either the surat jalan or moving number.
     *
     * This function deletes a record from the `invoices` table based on the provided `no_sj`. 
     * If `no_sj` does not contain "SJP", the function deletes based on `nomor_surat_jalan`. 
     * Otherwise, it deletes based on `no_moving`.
     *
     * @param string $no_sj The surat jalan or moving number for the invoice to delete.
     *
     * @return bool Returns `true` if the deletion is successful, or `false` if an error occurs.
     * 
     * @throws Exception Throws an exception if a foreign key constraint violation occurs.
     */
    function deleteInvoice($no_sj){
        global $db;
    
        if(!strpos($no_sj, "SJP")){
            $query = "DELETE FROM invoices WHERE nomor_surat_jalan = :no_sj";
            $statement = $db->prepare($query);
            $statement->bindValue(":no_sj", $no_sj);
        }
        else{
            $query = "DELETE FROM invoices WHERE no_moving = :no_sj";
            $statement = $db->prepare($query);
            $statement->bindValue(":no_sj", $no_sj);
        }
    
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