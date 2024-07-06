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

?>