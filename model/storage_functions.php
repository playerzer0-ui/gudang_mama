<?php 

require_once "database.php";

function getAllStorages(){
    global $db;

    $query = "SELECT * FROM storages";
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

?>