<?php
include("includes/header.php"); //Also includes classes like User and Post

// session_destroy();

$message_obj = new Message($con, $userLoggedIn);

// Profile actual url (we hid it with htaccess) /fb/profile.php?profile_username=rix_rix

if (isset($_GET['profile_username'])) {
    $username = $_GET['profile_username'];

    $user_details_query = mysqli_query($con, "SELECT * FROM users WHERE username='$username'");
    $user_array = mysqli_fetch_array($user_details_query);

    $num_friends = (substr_count($user_array['friend_array'], ",")) - 1;
}

if (isset($_POST['remove_friend'])) {
    $user = new User($con, $userLoggedIn);
    $user->removeFriend($username);
}

if (isset($_POST['add_friend'])) {
    $user = new User($con, $userLoggedIn);
    $user->sendRequest($username);
}

if (isset($_POST['respond_request'])) {
    header("Location: requests.php"); //Redirect to requests page!
}

if (isset($_POST['post_message'])) {
    if (isset($_POST['message_body'])) {
        $body = mysqli_real_escape_string($con, $_POST['message_body']);
        $date = date("Y-m-d H:i:s");
        $message_obj->sendMessage($username, $body, $date);
    }

    $link = '#profileTabs a[href="#messages_div"]';
    echo "
        <script>
            $(function() {
                $('" . $link . "').tab('show');
            });
        </script>
    ";
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

    <form action="<?php echo $username ?>" method="POST">

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
                echo '<input type="submit" name="remove_friend" class="danger" value="Remove friend"><br>';
            } else if ($logged_in_user_obj->didReceiveRequest($username)) {
                echo '<input type="submit" name="respond_request" class="warning" value="Respond to Request"><br>';
            } else if ($logged_in_user_obj->didSendRequest($username)) {
                echo '<input type="submit" name="" class="default" value="Request Sent"><br>';
            } else {
                echo '<input type="submit" name="add_friend" class="success" value="Add Friend"><br>';
            }
        }
        ?>

    </form>

    <input type="submit" class="deep_blue" data-toggle="modal" data-target="#post_form" value="Post Something">

    <?php
    //If logged in user is not on their own profile
    if ($userLoggedIn != $username) {
        echo '<div class="profile_info_bottom">';
        echo "Mutual friends: " . $logged_in_user_obj->getMutualFriends($username);
        echo "</div>";
    }
    ?>

</div>

<div class="profile_main_column column">

    <!-- TABS -->
    <ul class="nav nav-tabs" id="profileTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="home-tab" data-toggle="tab" href="#newsfeed_div" role="tab" aria-controls="newsfeed_div" aria-selected="true">Newsfeed</a>
        </li>
        <!-- <li class="nav-item">
            <a class="nav-link" id="profile-tab" data-toggle="tab" href="#about_div" role="tab" aria-controls="about_div" aria-selected="false">About</a>
        </li> -->
        <li class="nav-item">
            <a class="nav-link" id="contact-tab" data-toggle="tab" href="#messages_div" role="tab" aria-controls="messages_div" aria-selected="false">Messages</a>
        </li>
    </ul>

    <!-- TABS CONTENT -->
    <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade show active" id="newsfeed_div" role="tabpanel" aria-labelledby="home-tab">
            <div class="posts_area">
                <!-- Posts are going to be loaded via ajax, 10 at a time -->
            </div>
            <img id="loading" src="assets/images/icons/loading.gif" alt="Loading">
        </div>
        <!--  <div class="tab-pane fade" id="about_div" role="tabpanel" aria-labelledby="profile-tab">

        </div> -->
        <div class="tab-pane fade" id="messages_div" role="tabpanel" aria-labelledby="contact-tab">
            <?php   //GET MESSAGES

            $open_query = mysqli_query($con, "SELECT opened, id FROM messages WHERE user_from='$userLoggedIn' AND user_to='$username' ORDER BY id DESC LIMIT 1"); //my last message
            $latest_query_rec = mysqli_query($con, "SELECT id FROM messages WHERE user_to='$userLoggedIn' AND user_from='$username' ORDER BY id DESC LIMIT 1"); //friend's last message

            $check_mess = mysqli_fetch_array($open_query);
            $check_latest = mysqli_fetch_array($latest_query_rec);

            $seen = (isset($check_mess['opened']) && $check_mess['opened'] === 'yes') ? "Seen" : ""; //check if he opened my last message

            echo "<h4>&nbsp;You and <a href='" . $username . "'>" . $profile_user_obj->getFirstAndLastName() . "</a></h4><hr><br>";
            echo "<div class='loaded messages' id='scroll_messages'>";
            echo $message_obj->getMessages($username);

            if (isset($check_mess['id']) && isset($check_latest['id']) && $check_mess['id'] > $check_latest['id']) //check if mine is the last message in the conversation
                echo "<div style='float:right; position:relative; bottom:5px; right:3px;'>" . $seen . "</div><br>";

            echo "</div>";

            ?>

            <div class="message_post">
                <!-- action blank = this page -->
                <form action="" method="POST">
                    <?php

                    echo "<textarea name='message_body' id='message_textarea' placeholder='Write your message...'></textarea>";
                    echo "<input type='submit' name='post_message' class='info' id='message_submit' value='Send'>";

                    ?>
                </form>
            </div>

            <script>
                // Always scroll to most recent message
                var div = document.getElementById("scroll_messages");
                div.scrollTop = div.scrollHeight;
            </script>
        </div>
    </div>


</div>

<!-- Modal -->
<div class="modal fade" id="post_form" tabindex="-1" role="dialog" aria-labelledby="postModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Post Something!</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>This will appear on the user's profile page and also their newsfeed for your friends to see!</p>
                <form class="profile_post" action="" method="POST">
                    <div class="form-group">
                        <textarea class="form-control" name="post_body"></textarea>
                        <input type="hidden" name="user_from" value="<?php echo $userLoggedIn; ?>">
                        <input type="hidden" name="user_to" value="<?php echo $username; ?>">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" name="post_button" id="submit_profile_post">Post</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(function() {

        var userLoggedIn = '<?php echo $userLoggedIn; ?>'; //Save the session variable to js value, to be used with ajax $_REQUEST later
        var profileUsername = '<?php echo $username; ?>';

        var inProgress = false;

        loadPosts(); //Load first posts

        $(window).scroll(function() {
            var bottomElement = $(".status_post").last();
            var noMorePosts = $('.posts_area').find('.noMorePosts').val();

            // isElementInViewport uses getBoundingClientRect(), which requires the HTML DOM object, not the jQuery object. The jQuery equivalent is using [0] as shown below.
            if (isElementInView(bottomElement[0]) && noMorePosts == 'false') {
                loadPosts();
            }
        });

        function loadPosts() {
            if (inProgress) { //If it is already in the process of loading some posts, just return
                return;
            }

            inProgress = true;
            $('#loading').show();

            var page = $('.posts_area').find('.nextPage').val() || 1; //If .nextPage couldn't be found, it must not be on the page yet (it must be the first time loading posts), so use the value '1'

            $.ajax({
                url: "includes/handlers/ajax_load_profile_posts.php",
                type: "POST",
                data: "page=" + page + "&userLoggedIn=" + userLoggedIn + "&profileUsername=" + profileUsername,
                cache: false,

                success: function(response) {
                    $('.posts_area').find('.nextPage').remove(); //Removes current .nextpage 
                    $('.posts_area').find('.noMorePosts').remove(); //Removes current .nextpage 
                    $('.posts_area').find('.noMorePostsText').remove(); //Removes current .nextpage 

                    $('#loading').hide();
                    $(".posts_area").append(response);

                    inProgress = false;
                }
            });
        }

        //Check if the element is in view
        function isElementInView(el) {
            var rect = el.getBoundingClientRect();

            return (
                rect.top >= 0 &&
                rect.left >= 0 &&
                rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) && //* or $(window).height()
                rect.right <= (window.innerWidth || document.documentElement.clientWidth) //* or $(window).width()
            );
        }
    });
</script>


<!-- jquery js -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

<!-- Bootstrap js -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js" integrity="sha384-JEW9xMcG8R+pH31jmWH6WWP0WintQrMb4s7ZOdauHnUtxwoG2vI5DkLtS3qm9Ekf" crossorigin="anonymous"></script>
</div> <!-- End of wrapper div in header.php -->
</body>

</html>