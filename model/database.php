<?php

    $hostname = "localhost";
    $dbname = "database_gudang";
    $uname = "gudang_user";
    $password = "strong_password";

    $dsn = "mysql:host=" . $hostname . ";dbname=" . $dbname;


    try {
        $db = new PDO($dsn, $uname, $password);
        $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        error_reporting(E_ALL);
    }
    catch(PDOException $ex){
        echo "connection failed: " . $ex->getMessage();
    }

?>
