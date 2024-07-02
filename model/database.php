<?php

    if($_SERVER['HTTP_HOST'] == "localhost"){
        $hostname = "localhost";
        $dbname = "database_gudang";
        $uname = "root";
        $password = "";
    
        $dsn = "mysql:host=" . $hostname . ";dbname=" . $dbname;

    }
    else{
        $hostname = "mysql06host.comp.dkit.ie";
        $dbname = "D00234340";
        $uname = "D00234340";
        $password = "vtQwHz7j";
    
        $dsn = "mysql:host=" . $hostname . ";dbname=" . $dbname;
    }


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