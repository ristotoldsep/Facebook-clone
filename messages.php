<?php
include("includes/header.php"); //Also includes classes like User and Post

$message_obj = new Message($con, $userLoggedIn);

if (isset($_GET['u'])) { //U for username
    $user_to = $_GET['u'];
} else {
    $user_to = $message_obj->getMostRecentUser();

    //If user has not started a convo with anybody yet
    if ($user_to == false) {
        $user_to = 'new';
    }
}

if ($user_to != "new")
    $user_to_obj = new User($con, $user_to); /* you're not trying to send a new message then create a user object of the username that we have here */

if (isset($_POST['post_message'])) {
    if ( isset($_POST['message_body'])) {
        //Cancel out sql syntax
        $body = mysqli_real_escape_string($con, $_POST['message_body']);
        $date = date("Y-m-d H:i:s");
        $message_obj->sendMessage($user_to, $body, $date);
    }
}
?>

<div class="user_details column">
    <!-- comes from header page, rewrite in .htaccess -->
    <a href="<?php echo $userLoggedIn; ?>">
        <img src="<?php echo $user['profile_pic']; ?>" alt="Profile picture">
    </a>
    <div class="user_details_left_right">
        <a href="<?php echo $userLoggedIn; ?>">
            <?php
            echo $user['first_name'] . " " . $user['last_name'];
            ?>
        </a>
        <br>
        <?php
        echo "Posts: " . $user['num_posts'] . "<br>";
        echo "Likes: " . $user['num_likes'];
        ?>
    </div>
</div>

<div class="main_column column" id="main_column">
    <?php
        //GET MESSAGES
        if ($user_to != "new") {
            echo '<h4>You and <a href="' . $user_to .'">' . $user_to_obj->getFirstAndLastName() . '</a></h4><hr><br>';
            echo "<div class='loaded_messages' id='scroll_messages'>";
                echo $message_obj->getMessages($user_to);
            echo "</div>";
        }
        else {
            echo "<h4>New Message</h4>";
        }
    ?>

    <div class="message_post">
        <!-- action blank = this page -->
        <form action="" method="POST">
            <?php
                if ($user_to == "new") {
                    echo "Select the friend you would like to message <br><br>";
                    echo "To: <input type='text' >";
                    echo "<div class='results'></div>";
                }
                else {
                    echo "<textarea name='message_body' id='message_textarea' placeholder='Write your message...'></textarea>";
                    echo "<input type='submit' name='post_message' class='info' id='message_submit' value='Send'>";
                }
            ?>
        </form>
    </div>

    <script>
        // Always scroll to most recent message
        var div = document.getElementById("scroll_messages");
        div.scrollTop = div.scrollHeight;
    </script>

</div>
<div class="user_details column" id="conversations">
    <h4>Conversations</h4>

    <div class="loaded_conversations">
        <?php
        //Get conversations list
            echo $message_obj->getConvos();
        ?>
        <br>
        <a href="messages.php?u=new">New Message</a>
    </div>
</div>