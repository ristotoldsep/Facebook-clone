<?php 
ob_start(); //Turns on output buffering
session_start(); //Starts the session

$timezone = date_default_timezone_set("Europe/Helsinki");

$con = mysqli_connect('localhost', 'root', '', 'social');

if (mysqli_connect_errno()) {
    echo "Failed to connect: " . mysqli_connect_errno();
}
//$query = mysqli_query($con, "INSERT INTO test(name) VALUES ('Ricoboy')");
?>