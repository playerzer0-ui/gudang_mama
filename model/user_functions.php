<?php 

require_once "database.php";

/**
 * Registers a new user by inserting their credentials into the `user` table.
 *
 * This function hashes the provided password and inserts a new record with the username, hashed password,
 * and user type into the `user` table.
 *
 * @param string $username The username for the new user.
 * @param string $password The password for the new user.
 * @param string $userType The type of user (e.g., 'admin', 'user').
 *
 * @return void
 * @throws Exception If there is an issue with database interaction.
 */
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

/**
 * Authenticates a user by verifying their credentials.
 *
 * This function checks if the provided username exists and if the provided password matches the hashed password
 * in the database. If authentication is successful, it starts a session and stores user information in session variables.
 * Otherwise, it redirects to the login page with an appropriate error message.
 *
 * @param string $username The username of the user trying to log in.
 * @param string $password The password of the user trying to log in.
 *
 * @return void
 * @throws Exception If there is an issue with database interaction.
 */
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

/**
 * Logs out the current user by destroying the session.
 *
 * This function ends the user session and redirects to the index page.
 *
 * @return void
 */
function logout(){
    session_start();
    session_unset();
    session_destroy();
    header("Location:../controller/index.php?action=index");
}

/**
 * Checks if a username already exists in the `user` table.
 *
 * This function queries the `user` table to determine if the provided username is already in use.
 * If the username exists, it redirects to the registration page with an error message.
 *
 * @param string $username The username to check for existence.
 *
 * @return void
 * @throws Exception If there is an issue with database interaction.
 */
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