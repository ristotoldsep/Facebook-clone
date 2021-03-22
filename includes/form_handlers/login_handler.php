<?php 

if (isset($_POST['login_button'])) {
    $email = filter_var($_POST['log_email'], FILTER_SANITIZE_EMAIL); //sanitize email

    $SESSION['log_email'] = $email; //Store email into session variable
    $password = md5($_POST['log_password']); //Get password

    $check_database_query = mysqli_query($con, "SELECT * FROM users WHERE email='$email' AND password='$password'");

    $check_login_query = mysqli_num_rows($check_database_query); //Count the rows returned

    if ($check_login_query == 1) {
        $row = mysqli_fetch_array($check_database_query);
        $username = $row['username'];

        $user_closed_query = mysqli_query($con, "SELECT * FROM users WHERE email='$email' AND user_closed='yes'");

        //if user account closed, logging in will reopen it
        if (mysqli_num_rows($user_closed_query) == 1) {
            $reopen_account = mysqli_query($con, "UPDATE users SET user_closed='no' WHERE email='$email'");   
        }

        $_SESSION['username'] = $username; //Create a new user session with the username
        header('location: index.php'); //redirect to index page if logged in!
        exit();
    } else {
        array_push($error_array, "Email or password was incorrect<br>");
    }
}

?>