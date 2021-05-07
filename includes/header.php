<?php require 'config/config.php'; //getting $con var

//If user is logged in 
if (isset($_SESSION['username'])) {
    $userLoggedIn = $_SESSION['username'];

    //Get user details from db
    $user_details_query = mysqli_query($con, "SELECT * FROM users WHERE username='$userLoggedIn'");

    $user = mysqli_fetch_array($user_details_query); //return array from db (info about the logged in user)

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

    <!-- jquery js -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
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

        <!-- SEARCH FORM -->
        <div class="search">

            <form action="search.php" method="GET" name="search_form">
                <input type="text" onkeyup="getLiveSearchUsers(this.value, '<?php echo $userLoggedIn; ?>')" name="q" placeholder="Search..." autocomplete="off" id="search_text_input">

                <div class="button_holder">
                    <img src="assets/images/icons/magnifier.png" alt="">
                </div>
            </form>
            

            <div class="search_results"></div>

            <div class="search_results_footer_empty"></div>

        </div>

        <nav>
            <a href="<?php echo $userLoggedIn; ?>">
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
            <a href="includes/handlers/logout.php">
                <i class="fa fa-sign-out fa-lg" aria-hidden="true"></i>
            </a>
        </nav>
    </div>

    <div class="wrapper">