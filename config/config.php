<?php 
ob_start(); //Turns on output buffering
session_start(); //Starts the session

$timezone = date_default_timezone_set("Europe/Helsinki");

//IF DEVELOPMENT ENVIRONMENT - use local DB
if (strstr($_SERVER['SERVER_NAME'], 'localhost')) {
    $con = mysqli_connect('localhost', 'root', '', 'social');
}
else { //IF PRODUCTION ENVIRONMENT - Use DB from heroku
    //Get Heroku ClearDB connection information
    $cleardb_url = parse_url(getenv("CLEARDB_DATABASE_URL"));
    $cleardb_server = $cleardb_url["host"];
    $cleardb_username = $cleardb_url["user"];
    $cleardb_password = $cleardb_url["pass"];
    $cleardb_db = substr($cleardb_url["path"], 1);
    $active_group = 'default';
    $query_builder = TRUE;

    // Connect to DB
    $con = mysqli_connect($cleardb_server, $cleardb_username, $cleardb_password, $cleardb_db);
}

// Change character set to utf8
mysqli_set_charset($con, "utf8");

if (mysqli_connect_errno()) {
    echo "Failed to connect: " . mysqli_connect_errno();
}
//$query = mysqli_query($con, "INSERT INTO test(name) VALUES ('Ricoboy')");
?>