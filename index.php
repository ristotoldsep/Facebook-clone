<?php
include("includes/header.php"); //Header file with the db connection etc, also includes Classes like User and Post


if (isset($_POST['post'])) {

    $uploadOk = true;
    $imageName = $_FILES['fileToUpload']['name']; //Get file NAME
    $error_message = "";

    if ($imageName != "") {
        $targetDir = "assets/images/posts/";
        $imageName = $targetDir . uniqid() . basename($imageName); //So two people adding file with the same name does not get overwritten
        $imageFileType = pathinfo($imageName, PATHINFO_EXTENSION); //png, jpg etc...
        
        if ($_FILES['fileToUpload']['size'] > 1000000) {
            $errorMessage = "Sorry, your file is too large!";
            $uploadOk = false;
        }

        if (strtolower($imageFileType) != "jpeg" && strtolower($imageFileType) != "png" && strtolower($imageFileType) != "jpg") {
            $errorMessage = "Sorry, only jpeg, jpg and png files are allowed.";
            $uploadOk = false;
        }

        if ($uploadOk) {
            if (move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $imageName)) {
                //Image uploaded okay
            }
            else {
                //Img did not upload
                $uploadOk = false;
            }
        }
    }

    if ($uploadOk) {
        $post = new Post($con, $userLoggedIn); //Create a new post instance of this class, pass the user who created it

        $post->submitPost($_POST['post_text'], 'none', $imageName); //Submit the post via submit method in the Post.php class file, $user_to is none cause it is the index page

        header("location: index.php"); //Removes the resubmission of form when refreshing the page!
    }
    else {
        echo "<div style='text-align: center;' class='alert alert-danger'>
            " . $errorMessage . "
        </div>";
    }

    
}

?>
<!-- USER DETAILS -->
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

<!-- MAIN COLUMN -->
<div class="main_column column">
    <!-- enctype allows forms to process file data -->
    <form class="post_form" action="index.php" method="POST" enctype="multipart/form-data">
        <input type="file" name="fileToUpload" id="fileToUpload"><br><br>
        <textarea name="post_text" id="post_text" placeholder="Got something to say?"></textarea>
        <input type="submit" name="post" id="post_button" value="Post">
        <hr>
    </form>

    <?php
    /* $post = new Post($con, $userLoggedIn); //Create a new post instance of this class, pass the user who created it
        $post->loadPostsFriends(); */
    ?>

    <div class="posts_area">
        <!-- Posts are going to be loaded via ajax, 10 at a time -->
    </div>
    <img id="loading" src="assets/images/icons/loading.gif" alt="Loading">
</div>

<div class="user_details column">
    <h4>Trending</h4>
    <div class="trends">
        <?php
            $query = mysqli_query($con, "SELECT * FROM trends ORDER BY hits DESC LIMIT 9");

            foreach($query as $row) {

                $word = $row['title'];
                $word_dot = strlen($word) >= 14 ? "..." : ""; //Append ... if word is too long

                $trimmed_word = str_split($word, 14);
                $trimmed_word = $trimmed_word[0];

                echo "<div style='padding: 1px;'>";
                echo $trimmed_word . $word_dot;
                echo "<br></div>";
            }
        ?>
    </div>
</div>

<!-- INFINITE SCROLLING -->
<script>
    $(function() {

        var userLoggedIn = '<?php echo $userLoggedIn; ?>'; //Save the session variable to js value, to be used with ajax $_REQUEST later
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
                url: "includes/handlers/ajax_load_posts.php",
                type: "POST",
                data: "page=" + page + "&userLoggedIn=" + userLoggedIn,
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


<script>
    /* //Username of the logged in user
    let userLoggedIn = '<?php //echo $userLoggedIn; 
                        ?>';

    $(document).ready(function() {
        $('#loading').show();

        //Original ajax request for loading first posts
        $.ajax({
            url: "includes/handlers/ajax_load_posts.php",
            type: "POST",
            data: "page=1&userLoggedIn=" + userLoggedIn,
            cache: false,

            success: function(data) {
                $('#loading').hide();
                $('.posts_area').html(data);
            }
        });

        $(window).scroll(function() {
            let height = $('.posts_area').height(); //div containing posts
            let scroll_top = $(this).scrollTop();
            let page = $('.posts_area').find('.nextPage').val();
            let noMorePosts = $('.posts_area').find('.noMorePosts').val();

            //When user scrolls and more posts have to be loaded
            if ((document.body.scrollHeight == document.body.scrollTop + window.innerHeight) && noMorePosts == 'false') {
                $('#loading').show();

                let ajaxReq = $.ajax({
                    url: "includes/handlers/ajax_load_posts.php",
                    type: "POST",
                    data: "page=" + page + "&userLoggedIn=" + userLoggedIn,
                    cache: false,

                    success: function(response) {
                        $('.posts_area').find('.nextPage').remove(); //Removes current .nextPage
                        $('.posts_area').find('.noMorePosts').remove(); //Removes current .nextPage

                        $('#loading').hide();
                        $('.posts_area').append(response);
                    }
                });
            } //End if

            return false; //If there are no more posts

        }); //End  $(window).scroll(function())
    }); */
</script>

</div> <!-- End of wrapper div in header.php -->
</body>

</html>