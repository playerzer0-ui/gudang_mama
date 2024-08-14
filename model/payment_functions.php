<?php 

    require_once "database.php";


    /**
     * Inserts a new payment record into the `payments` table.
     *
     * This function adds a new payment record with the specified details to the `payments` table.
     *
     * @param string $nomor_surat_jalan The document number for the payment.
     * @param string $payment_date The date of the payment.
     * @param float $payment_amount The amount of the payment.
     * @param string $no_moving The moving number associated with the payment.
     *
     * @return void
     */
    function create_payment($nomor_surat_jalan, $payment_date, $payment_amount, $no_moving){
        global $db;
    
        $query = 'INSERT INTO payments
            VALUES (:nomor_surat_jalan, :payment_date, :payment_amount, :no_moving, UUID())';
    
        $statement = $db->prepare($query);
        $statement->bindValue(":nomor_surat_jalan", $nomor_surat_jalan);
        $statement->bindValue(":payment_date", $payment_date);
        $statement->bindValue(":payment_amount", $payment_amount);
        $statement->bindValue(":no_moving", $no_moving);
    
        try {
            $statement->execute();
        }
        catch(PDOException $ex){
            $ex->getMessage();
        }
    
        $statement->closeCursor();
    }

    /**
     * Retrieves all payment records from the `payments` table.
     *
     * This function fetches all records from the `payments` table.
     *
     * @return array Returns an array of associative arrays, each containing details of a payment.
     */
    function getAllPayments(){
        global $db;

        $query = "SELECT * FROM payments";
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
     * Retrieves a specific payment record based on the provided payment ID.
     *
     * This function fetches details of a payment record from the `payments` table using the provided payment ID.
     *
     * @param string $payment_id The ID of the payment to retrieve.
     *
     * @return array|null Returns an associative array containing the payment record details, or `null` if not found.
     */
    function getPaymentByID($payment_id){
        global $db;

        $query = "SELECT * FROM payments WHERE payment_id = :payment_id";
        $statement = $db->prepare($query);
        $statement->bindValue(":payment_id", $payment_id);

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
     * Calculates the total payment amount based on the provided document number.
     *
     * This function sums up the payment amounts from the `payments` table based on whether the 
     * document number is for a specific `SJP` or a `moving` record.
     *
     * @param string $nomor_surat_jalan The document number to sum payments for.
     *
     * @return array Returns an associative array with a `totalPayment` key, representing the sum of payments.
     */
    function getTotalPayment($nomor_surat_jalan){
        global $db;

        if(!strpos($nomor_surat_jalan, "SJP")){
            $query = "SELECT SUM(payment_amount) AS totalPayment FROM payments WHERE nomor_surat_jalan = :nomor_surat_jalan";
        }
        else{
            $query = "SELECT SUM(payment_amount) AS totalPayment FROM payments WHERE no_moving = :nomor_surat_jalan";
        }
        $statement = $db->prepare($query);
        $statement->bindValue(":nomor_surat_jalan", $nomor_surat_jalan);

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
     * Updates an existing payment record in the `payments` table.
     *
     * This function modifies an existing payment record based on the provided new payment details.
     * It identifies the record to update by the payment ID.
     *
     * @param string $nomor_surat_jalan The new document number for the payment.
     * @param string $payment_date The new date of the payment.
     * @param float $payment_amount The new amount of the payment.
     * @param string $no_moving The new moving number associated with the payment.
     * @param string $payment_id The ID of the payment record to update.
     *
     * @return bool|string Returns `true` if the update is successful, `false` if an error occurs,
     *                     or a string ('foreign_key') if a foreign key constraint error is encountered.
     */
    function updatePayment($nomor_surat_jalan, $payment_date, $payment_amount, $no_moving, $payment_id){
        global $db;
    
        $query = "UPDATE payments SET payment_date = :payment_date, payment_amount = :payment_amount, nomor_surat_jalan = :nomor_surat_jalan, no_moving = :no_moving WHERE payment_id = :payment_id";
        
        $statement = $db->prepare($query);
        $statement->bindValue(":payment_date", $payment_date);
        $statement->bindValue(":payment_amount", $payment_amount);
        $statement->bindValue(":nomor_surat_jalan", $nomor_surat_jalan);
        $statement->bindValue(":no_moving", $no_moving);
        $statement->bindValue(":payment_id", $payment_id);
    
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
     * Deletes a specific payment record from the `payments` table by payment_id (UUID).
     *
     * This function removes a payment record from the `payments` table based on the provided payment ID.
     *
     * @param string $payment_id The ID of the payment record to delete.
     *
     * @return bool|string Returns `true` if the deletion is successful, `false` if an error occurs,
     *                     or a string ('foreign_key') if a foreign key constraint violation is encountered.
     */
    function deletePayment($payment_id){
        global $db;
    
        $query = "DELETE FROM payments WHERE payment_id = :payment_id";
        $statement = $db->prepare($query);
        $statement->bindValue(":payment_id", $payment_id);
    
        try {
            $statement->execute();
            $statement->closeCursor();
            return true;
        } catch (PDOException $ex) {
            $errorCode = $ex->getCode();
            if ($errorCode == 23000) {
                $errorInfo = $ex->errorInfo;
                if (strpos($errorInfo[2], 'foreign key constraint fails') !== false) {
                    throw new Exception($ex->getMessage());
                }
            }
            return false;
        }
    }    

    /**
     * Deletes multiple payment records based on the provided document number.
     *
     * This function removes all payment records associated with the given document number from the `payments` table.
     *
     * @param string $nomor_surat_jalan The document number to delete associated payments for.
     *
     * @return bool|string Returns `true` if the deletion is successful, `false` if an error occurs,
     *                     or a string ('foreign_key') if a foreign key constraint violation is encountered.
     */
    function deleteMultiPayment($nomor_surat_jalan){
        global $db;
    
        $query = "DELETE FROM payments WHERE nomor_surat_jalan = :nomor_surat_jalan";
        $statement = $db->prepare($query);
        $statement->bindValue(":nomor_surat_jalan", $nomor_surat_jalan);
    
        try {
            $statement->execute();
            $statement->closeCursor();
            return true;
        } catch (PDOException $ex) {
            $errorCode = $ex->getCode();
            if ($errorCode == 23000) {
                $errorInfo = $ex->errorInfo;
                if (strpos($errorInfo[2], 'foreign key constraint fails') !== false) {
                    throw new Exception($ex->getMessage());
                }
            }
            return false;
        }
    }    
?>