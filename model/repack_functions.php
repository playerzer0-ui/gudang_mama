<?php

    require_once "database.php";

    function generate_SJR($storageCode){
        global $db;

        $query = 'SELECT count(*) AS totalIN FROM repacks WHERE month(repack_date) = :mon AND year(repack_date) = :yea AND no_repack LIKE "%SJR%"';
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

        return $no . "/SJR/" . $storageCode . "/" . date("m") . "/" . date("Y");
    }

    function create_repack($storageCode, $repack_date, $no_repack){
        global $db;

        $query = 'INSERT INTO repacks VALUES (:no_repack, :repack_date, :storageCode)';
        $statement = $db->prepare($query);
        $statement->bindValue(":no_repack", $no_repack);
        $statement->bindValue(":repack_date", $repack_date);
        $statement->bindValue(":storageCode", $storageCode);

        try {
            $statement->execute();
        }
        catch(PDOException $ex){
            $ex->getMessage();
        }

        $statement->closeCursor();
    }

?>