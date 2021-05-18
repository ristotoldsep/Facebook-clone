<?php

include("includes/header.php");

?>

<div class="main_column column" id="main_column">
    <h4>Requests sent</h4><br>
    <?php
    $pending = mysqli_query($con, "SELECT user_to, id FROM friend_requests WHERE user_from='$userLoggedIn'");
    if (mysqli_num_rows($pending) > 0) {
        while ($row = mysqli_fetch_array($pending)) {
            $sent_to = $row['user_to'];

            $req_id = $row['id'];
            $delete_button = "<button class='btn-danger delReq' id='$req_id'>Cancel request</button>";

            $data_query = mysqli_query($con, "SELECT * FROM users WHERE username='$sent_to'");

            while ($user = mysqli_fetch_array($data_query)) {
                echo "<a href='" . $user['username'] . "'>
                        <img src='" . $user['profile_pic'] . "' style='height: 45px;'>
                        </a>
                        <a href='" . $user['username'] . "'>
                            " . $user['first_name'] . " " . $user['last_name'] . "
                        </a>$delete_button<br><hr>";
            }
        }
    } else {
        echo "<p>You have no pending requests right now.<br>";
    }
    ?>
    <script>
        $(function() {
            $(".delReq").on('click', function(e) {
                let id = e.target.id;
                $.post("includes/handlers/delete_request.php", {
                    id: id
                }, function() {
                    location.reload();
                });
            });
        });
    </script>
    <hr>
    <h4>Friend Requests</h4><br>
    <?php
    $query = mysqli_query($con, "SELECT * FROM friend_requests WHERE user_to='$userLoggedIn'");
    if (mysqli_num_rows($query) == 0) {
        echo "You have no friend requests at the moment!<br><br>";
    } else {
        while ($row = mysqli_fetch_array($query)) {
            $user_from = $row['user_from'];
            $user_from_obj = new User($con, $user_from);

            echo $user_from_obj->getFirstAndLastName() . " sent you a friend request!";

            $user_from_friend_array = $user_from_obj->getFriendArray();

            if (isset($_POST['accept_request' . $user_from])) {
                $add_friend_query = mysqli_query($con, "UPDATE users SET friend_array=CONCAT(friend_array, '$user_from,') WHERE username='$userLoggedIn'"); //Add friend to database for both the sender and receiver
                $add_friend_query = mysqli_query($con, "UPDATE users SET friend_array=CONCAT(friend_array, '$userLoggedIn,') WHERE username='$user_from'");

                $delete_query = mysqli_query($con, "DELETE FROM friend_requests WHERE user_to='$userLoggedIn' AND user_from='$user_from'");
                echo "You are now friends!";
                header("Location: requests.php");
            }

            if (isset($_POST['ignore_request' . $user_from])) {
                $delete_query = mysqli_query($con, "DELETE FROM friend_requests WHERE user_to='$userLoggedIn' AND user_from='$user_from'");
                echo "Request ignored!";
                header("Location: requests.php");
            }
    ?>

            <form action="requests.php" method="POST">
                <input type="submit" name="accept_request<?php echo $user_from; ?>" id="accept_button" value="Accept">
                <input type="submit" name="ignore_request<?php echo $user_from; ?>" id="ignore_button" value="Ignore">
            </form>

    <?php
        } //End of while loop
    }
    ?>



</div>