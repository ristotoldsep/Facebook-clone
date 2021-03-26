<?php require 'config/config.php'; //getting $con var

//If user is logged in 
if (isset($_SESSION['username'])) {
    $userLoggedIn = $_SESSION['username'];

    //Get user details from db
    $user_details_query = mysqli_query($con, "SELECT * FROM users WHERE username='$userLoggedIn'");

    $user = mysqli_fetch_array($user_details_query); //return array from db

} else {
    header("Location: register.php"); //If not logged in, redirect to register
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facebook</title>

    <!-- Font awesome -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">
    <!-- My CSS -->
    <link rel="stylesheet" href="assets/css/style.css">

</head>

<body>

    <div class="top_bar">
        <div class="logo">
            <a href="index.php">Facebook</a>
        </div>

        <nav>
            <a href="#">
                <?php 
                    echo $user['username'];
                ?>
            </a>
            <a href="index.php">
                <i class="fa fa-home fa-lg" aria-hidden="true"></i>
            </a>
            <a href="#">
                <i class="fa fa-envelope fa-lg" aria-hidden="true"></i>
            </a>
            <a href="#">
                <i class="fa fa-bell-o fa-lg" aria-hidden="true"></i>
            </a>
            <a href="#">
                <i class="fa fa-users fa-lg" aria-hidden="true"></i>
            </a>
            <a href="#">
                <i class="fa fa-cog fa-lg" aria-hidden="true"></i>
            </a>
            <a href="#">
                <i class="fa fa-users fa-lg" aria-hidden="true"></i>
            </a>
        </nav>
    </div>