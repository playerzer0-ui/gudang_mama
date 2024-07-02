<?php 

require_once "database.php";

function register($username, $password, $userType){
    global $db;

    checkUsername($username);

    $hash_password = password_hash($password, PASSWORD_BCRYPT);
    $query = "INSERT INTO user (username, password, userType) VALUES (:username, :hash_password, :userType)";
    $statement = $db->prepare($query);
    $statement->bindValue(":username", $username);
    $statement->bindValue(":hash_password", $hash_password);
    $statement->bindValue(":userType", $userType);

    try {
        $statement->execute();
    }
    catch(PDOException $ex){
        $ex->getMessage();
    }

    $statement->closeCursor();
}

function login($username, $password){
    global $db;

    $query = "SELECT * FROM user WHERE username = :username";

    $statement = $db->prepare($query);
    $statement->bindValue(":username", $username);

    try {
        $statement->execute();
    }
    catch(PDOException $ex){
        $ex->getMessage();
    }

    $row = $statement->rowCount();

    //no user found
    if($row == 0){
        header("Location:../controller/index.php?action=login&msg=user not found");
        exit();
    }

    $results = $statement->fetch();
    $dbid = $results["userID"];
    $dbusername = $results['username'];
    $dbpassword = $results['password'];
    $dbuserType = $results['userType'];
    $statement->closeCursor();

    //if match, enter
    if(password_verify($password, $dbpassword)){
        session_start();
        $_SESSION["userID"] = $dbid;
        $_SESSION['username'] = $dbusername;
        $_SESSION['password'] = $password;
        $_SESSION['userType'] = $dbuserType;
    }
    else{
        header("Location:../controller/index.php?action=login&msg=invalid credentials");
        exit();
    }
}

function logout(){
    session_start();
    session_unset();
    session_destroy();
    header("Location:../controller/index.php?action=index");
}

function checkUsername($username){
    global $db;

    $query = "SELECT * FROM user WHERE username = :username";
    $statement = $db->prepare($query);
    $statement->bindValue(":username", $username);

    try {
        $statement->execute();
    }
    catch(PDOException $ex){
        $ex->getMessage();
    }

    $row = $statement->rowCount();

    if($row != 0){
        //same username detected, error
        header("Location:../controller/index.php?action=register&msg=there is already an exact username, try a different one");
        exit();
    }

    $statement->closeCursor();
}

?>