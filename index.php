<?php
include("includes/header.php"); //Header file with the db connection etc
include("includes/classes/User.php"); //Call in the USER CLASS
include("includes/classes/Post.php"); //Call in the Post CLASS

if (isset($_POST['post'])) {
    $post = new Post($con, $userLoggedIn); //Create a new post instance of this class, pass the user who created it

    $post->submitPost($_POST['post_text'], 'none'); //Submit the post via submit method in the Post.php class file, $user_to is none cause it is the index page

    header("location: index.php"); //Removes the resubmission of form when refreshing the page!
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

<div class="main_column column">
    <form class="post_form" action="index.php" method="POST">
        <textarea name="post_text" id="post_text" placeholder="Got something to say?"></textarea>
        <input type="submit" name="post" id="post_button" value="Post">
        <hr>
    </form>

    <?php
        /* Created a new user object instance from constructor, and then I can access all the public methods etc */
        $user_obj = new User($con, $userLoggedIn);
        echo $user_obj->getFirstAndLastName();
    ?>

</div>


<!-- jquery js -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

<!-- Bootstrap js -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js" integrity="sha384-JEW9xMcG8R+pH31jmWH6WWP0WintQrMb4s7ZOdauHnUtxwoG2vI5DkLtS3qm9Ekf" crossorigin="anonymous"></script>
</div> <!-- End of wrapper div in header.php -->
</body>

</html>