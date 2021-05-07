<?php
include("includes/header.php");
include("includes/classes/User.php"); //Call in the USER CLASS
include("includes/classes/Post.php"); //Call in the Post CLASS
// session_destroy();

// Profile actual url (we hid it with htaccess) /fb/profile.php?profile_username=rix_rix

if (isset($_GET['profile_username'])) {
    $username = $_GET['profile_username'];

    $user_details_query = mysqli_query($con, "SELECT * FROM users WHERE username='$username'");
    $user_array = mysqli_fetch_array($user_details_query);

    $num_friends = (substr_count($user_array['friend_array'], ",")) - 1;
}

?>

<style>
    .wrapper {
        margin-left: 0px;
        padding-left: 0px;
    }
</style>

<!-- PROFILE BOX -->
<div class="profile_left">
    <img src="<?php echo $user_array['profile_pic']; ?>" alt="profile_pic">

    <div class="profile_info">
        <p><?php echo "Posts: " . $user_array['num_posts']; ?></p>
        <p><?php echo "Likes: " . $user_array['num_likes']; ?></p>
        <p><?php echo "Friends: " . $num_friends; ?></p>
    </div>

    <form action="<?php echo $username ?>">

        <?php
            $profile_user_obj = new User($con, $username);

            // Check if user is closed
            if ($profile_user_obj->isClosed()) {
                header("Location: user_closed.php");
            }

            $logged_in_user_obj = new User($con, $userLoggedIn);

            // Don't show add friend button on own profile
            if ($userLoggedIn != $username) {

                if ($logged_in_user_obj->isFriend($username)) {
                    echo '<input type="submit" name="remove_friend" class="danger" value="Remove friend"';
                } 
                else if ($logged_in_user_obj->didReceiveRequest($username)) {
                    echo '<input type="submit" name="respond_request" class="warning" value="Respond to Request"';
                }
                else if ($logged_in_user_obj->didSendRequest($username)) {
                    echo '<input type="submit" name="" class="default" value="Request Sent"';
                }
                else {
                    echo '<input type="submit" name="add_friend" class="success" value="Add Friend"';
                }
            }
        ?>
        
    </form>

</div>

<div class="main_column column">
    Welcome to profile of <?php echo $user_array['username']; ?>
</div>


<!-- jquery js -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

<!-- Bootstrap js -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js" integrity="sha384-JEW9xMcG8R+pH31jmWH6WWP0WintQrMb4s7ZOdauHnUtxwoG2vI5DkLtS3qm9Ekf" crossorigin="anonymous"></script>
</div> <!-- End of wrapper div in header.php -->
</body>

</html>