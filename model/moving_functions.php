<?php

    require_once "database.php";

    function generate_SJP($storageCode){
        global $db;

        $query = 'SELECT count(*) AS totalIN FROM movings WHERE month(moving_date) = :mon AND year(moving_date) = :yea AND no_repack LIKE "%SJP%"';
        $statement = $db->prepare($query);
        $statement->bindValue(":mon", date("m"));
        $statement->bindValue(":yea", date("Y"));

        try {
            $statement->execute();
        }
        catch(PDOException $ex){
            $ex->getMessage();
        }

        $result = $statement->fetch(PDO::FETCH_ASSOC);
        $no = $result["totalIN"] + 1;

        $statement->closeCursor();

        return $no . "/SJP/" . $storageCode . "/" . date("m") . "/" . date("Y");
    }

    function create_moving($no_moving, $moving_date, $storageCodeSender, $storageCodeReceiver){
        global $db;

        $query = 'INSERT INTO movings VALUES (:no_moving, :moving_date, :storageCodeSender, :storageCodeReceiver)';
        $statement = $db->prepare($query);
        $statement->bindValue(":no_moving", $no_moving);
        $statement->bindValue(":moving_date", $moving_date);
        $statement->bindValue(":storageCodeSender", $storageCodeSender);
        $statement->bindValue(":storageCodeReceiver", $storageCodeReceiver);

        try {
            $statement->execute();
        }
        catch(PDOException $ex){
            $ex->getMessage();
        }

        $statement->closeCursor();
    }

?>