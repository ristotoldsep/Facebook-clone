<?php
//Objecto oriented PHP - User class
class Post
{
    //Will only be able to use these variables inside this class
    private $user_obj;
    private $con;

    //Constructor (like in react) - creates the user object upon call = $new_obj = new User($con, $userLoggedIn) ...
    public function __construct($con, $user)
    {
        //"THIS" REFERENCES THE CLASS OBJECT
        $this->con = $con;
        $this->user_obj = new User($con, $user); //With each post, create a new instance of User class
    }

    public function submitPost($body, $user_to)
    {
        // $userLoggedIn = $this->user_obj->getUsername(); //Get logged in user 

        $body = strip_tags($body); //Remove html tags
        $body = mysqli_real_escape_string($this->con, $body); //Allow single quotes in strings etc (db will not act on them)

        $body = str_replace('\r\n', '\n', $body); //\r\n = Enter/linebreaks, we want to replace them with just \n //TO ALLOW USERS POST WITH LINEBREAKS
        $body = nl2br($body); //Replace the php "newline" \n with html <br> !!

        $check_empty = preg_replace('/\s+/', '', $body); //Deletes all spaces from body

        //Does not let the user enter just spaces into the db
        if ($check_empty != "") {

            //Current date & time
            $date_added = date("Y-m-d H:i:s");
            //Get username
            $added_by = $this->user_obj->getUsername(); //Get the getusername method

            //If user is on own profile, user_to is 'none'
            if ($user_to == $added_by) {
                $user_to = "none";
            }

            //Insert post to db
            $query = mysqli_query($this->con, "INSERT INTO posts VALUES ('', '$body', '$added_by', '$user_to', '$date_added', 'no', 'no', '0')");

            $returned_id = mysqli_insert_id($this->con); //Returns the id of the post submitted

            //Insert notification
            if ($user_to != "none") {
                $notification = new Notification($this->con, $added_by);
                $notification->insertNotification($returned_id, $user_to, 'profile_post');
            }

            //Update post count for user
            $num_posts = $this->user_obj->getNumPosts(); //Return the number of posts
            $num_posts++; //Increment the post count for the user
            $update_query = mysqli_query($this->con, "UPDATE users SET num_posts='$num_posts' WHERE username='$added_by'"); //Update the user 
        }
    }

    public function loadPostsFriends($data, $limit)
    {

        $page = $data['page'];
        $userLoggedIn = $this->user_obj->getUsername();

        if ($page == 1) {
            $start = 0;
        } else {
            $start = ($page - 1) * $limit;
        }

        $html = ""; //HTml to return in the end
        $data_query = mysqli_query($this->con, "SELECT * FROM posts WHERE deleted='no' ORDER BY id DESC"); //Latest first

        if (mysqli_num_rows($data_query) > 0) { //If at least one row is sent back from db

            $num_iterations = 0; //Number of results checked (not necessarily posted)
            $count = 1;

            while ($row = mysqli_fetch_array($data_query)) {
                $id = $row['id'];
                $body = $row['body'];
                $added_by = $row['added_by'];
                $date_time = $row['date_added'];

                //Prepare user_to string so it cn be included even if not posted to a user
                if ($row['user_to'] == "none") {
                    $user_to = "";
                } else {
                    $user_to_obj = new User($this->con, $row['user_to']); //New instance of the user to object (To post on someones page!!)
                    $user_to_name = $user_to_obj->getFirstAndLastName();
                    $user_to = "to <a href='" . $row['user_to'] . "'>" . $user_to_name . "</a>";
                }

                //Check if user who posted, has their account closed
                $added_by_obj = new User($this->con, $added_by);
                if ($added_by_obj->isClosed()) {
                    continue; //go to the next iteration of the loop, if user account is closed
                }

                //To show only friends posts on feed!!
                $user_logged_obj = new User($this->con, $userLoggedIn);

                if ($user_logged_obj->isFriend($added_by)) {

                    if ($num_iterations++ < $start) {
                        continue;
                    }

                    //Once 10 posts have been loaded, break
                    if ($count > $limit) {
                        break;
                    } else {
                        $count++;
                    }

                    // Delete post button
                    if ($userLoggedIn == $added_by)
                        $delete_button = "<button class='delete_button btn-danger' id='post$id'>X</button>";
                    else
                        $delete_button = "";

                    $user_details_query = mysqli_query($this->con, "SELECT first_name, last_name, profile_pic FROM users WHERE username='$added_by'");
                    $user_row = mysqli_fetch_array($user_details_query);

                    $first_name = $user_row['first_name'];
                    $last_name = $user_row['last_name'];
                    $profile_pic = $user_row['profile_pic'];

?>
                    <!-- COMMENTS BLOCK TOGGLE FUNCTION -->
                    <script>
                        function toggle<?php echo $id; ?>(event) {

                            var target = $(event.target);

                            if (!target.is('a') && !target.is('button')) {
                                var element = document.getElementById("toggleComment<?php echo $id; ?>");

                                if (element.style.display == "block")
                                    element.style.display = "none";
                                else
                                    element.style.display = "block";
                            }

                        }
                    </script>
                <?php

                    //Get the comment number count
                    $comments_check = mysqli_query($this->con, "SELECT * FROM comments WHERE post_id='$id'");
                    $comments_check_num = mysqli_num_rows($comments_check);

                    //Timeframe
                    $date_time_now = date("Y-m-d H:i:s");
                    $start_date = new DateTime($date_time); //Time of post
                    $end_date = new DateTime($date_time_now); //Current time
                    $interval = $start_date->diff($end_date); //Difference between dates

                    if ($interval->y >= 1) {
                        if ($interval->y == 1) {
                            $time_message = $interval->y . " year ago"; //1 year ago
                        } else {
                            $time_message = $interval->y . " years ago"; //.. years ago
                        }
                    } else if ($interval->m >= 1) {
                        if ($interval->d == 0) {
                            $days = " ago";
                        } else if ($interval->d == 1) {
                            $days = $interval->d . " day ago";
                        } else {
                            $days = $interval->d . " days ago";
                        }

                        if ($interval->m == 1) {
                            $time_message = $interval->m . " month " . $days;
                        } else {
                            $time_message = $interval->m . " months " . $days;
                        }
                    } else if ($interval->d >= 1) {
                        if ($interval->d == 1) {
                            $time_message = "Yesterday";
                        } else {
                            $time_message = $interval->d . " days ago";
                        }
                    } else if ($interval->h >= 1) {
                        if ($interval->h == 1) {
                            $time_message = $interval->h . " hour ago";
                        } else {
                            $time_message = $interval->h . " hours ago";
                        }
                    } else if ($interval->i >= 1) {
                        if ($interval->i == 1) {
                            $time_message = $interval->i . " minute ago";
                        } else {
                            $time_message = $interval->i . " minutes ago";
                        }
                    } else {
                        if ($interval->s < 30) {
                            $time_message = "Just now";
                        } else {
                            $time_message = $interval->s . " seconds ago";
                        }
                    }

                    //With each iteration, add a post to the html
                    $html .= "<div class='status_post' onClick='javascript:toggle$id(event)'>
                                <div class='post_profile_pic'>
                                    <img src='$profile_pic' width='50'>
                                </div>

                                <div class='posted_by' style='color: #ACACAC;'>
                                    <a href='$added_by'>$first_name $last_name </a> $user_to &nbsp;&nbsp;&nbsp;$time_message
                                    $delete_button
                                </div>
                                <div id='post_body'>
                                    $body
                                    <br>
                                    <br>
                                    <br>
                                </div>

                                <div class='newsfeedPostOptions'>
                                    Comments($comments_check_num)&nbsp;&nbsp;&nbsp;
                                    <iframe src='like.php?post_id=$id' scrolling='no'></iframe>
                                </div>
                            </div>
                            <div class='post_comment' id='toggleComment$id' style='display:none;'>
                                <iframe src='comment_frame.php?post_id=$id' id='comment_iframe' frameborder='0'></iframe>
                            </div>
                            <hr>";
                }
                ?>
                <script>
                    /* var deletePost = document.querySelector('#post<?php echo $id; ?>');
                    deletePost.addEventListener("click", function() {
                        alert("test <?php //echo $id; 
                                    ?>");
                    }); */
                    $(document).ready(function() {
                        $('#post<?php echo $id; ?>').on('click', function() {
                            // Comes with bootstrap JS
                            bootbox.confirm("Are you sure you want to delete this post?", function(result) {
                                $.post("includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>", {
                                    result: result
                                });

                                if (result) { //if result is true/exists
                                    location.reload()
                                }
                            });
                        });
                    });
                </script>
            <?php

            } //END WHILE LOOP

            //If there is some posts still left after 10 have been loaded - for ajax 
            if ($count > $limit) {
                //Increase the "page" size with a hidden page number, so ajax would load the next posts
                $html .= "<input type='hidden' class='nextPage' value='" . ($page + 1) . "'> 
                          <input type='hidden' class='noMorePosts' value='false'>"; //Hidden input to keep track of a value
            } else {
                if ($num_iterations == 0) {
                    $html .= "<input type='hidden' class='noMorePosts' value='true'><p style='text-align:center;'>No posts to show!</p>"; //Hidden input to keep track 
                } else {
                    //If there are no more posts to show, let the user know
                    $html .= "<input type='hidden' class='noMorePosts' value='true'><p style='text-align:center;'>No more posts to show!</p>"; //Hidden input to keep track of a value
                }
            }
        }
        //When the loop is done, echo the html
        echo $html;
    }

    public function loadProfilePosts($data, $limit) {

        $page = $data['page'];
        $profileUser = $data['profileUsername'];

        $userLoggedIn = $this->user_obj->getUsername();

        if ($page == 1) {
            $start = 0;
        } else {
            $start = ($page - 1) * $limit;
        }

        $html = ""; //HTml to return in the end
        $data_query = mysqli_query($this->con, "SELECT * FROM posts WHERE deleted='no' AND ((added_by='$profileUser' AND user_to='none') OR user_to='$profileUser') ORDER BY id DESC"); //Latest first

        if (mysqli_num_rows($data_query) > 0) { //If at least one row is sent back from db

            $num_iterations = 0; //Number of results checked (not necessarily posted)
            $count = 1;

            while ($row = mysqli_fetch_array($data_query)) {
                $id = $row['id'];
                $body = $row['body'];
                $added_by = $row['added_by'];
                $date_time = $row['date_added'];

                if ($num_iterations++ < $start) {
                    continue;
                }

                //Once 10 posts have been loaded, break
                if ($count > $limit) {
                    break;
                } else {
                    $count++;
                }

                // Delete post button
                if ($userLoggedIn == $added_by)
                    $delete_button = "<button class='delete_button btn-danger' id='post$id'>X</button>";
                else
                    $delete_button = "";

                $user_details_query = mysqli_query($this->con, "SELECT first_name, last_name, profile_pic FROM users WHERE username='$added_by'");
                $user_row = mysqli_fetch_array($user_details_query);

                $first_name = $user_row['first_name'];
                $last_name = $user_row['last_name'];
                $profile_pic = $user_row['profile_pic'];

            ?>
                <!-- COMMENTS BLOCK TOGGLE FUNCTION -->
                <script>
                    function toggle<?php echo $id; ?>(event) {

                        var target = $(event.target);

                        if (!target.is('a') && !target.is('button')) {
                            var element = document.getElementById("toggleComment<?php echo $id; ?>");

                            if (element.style.display == "block")
                                element.style.display = "none";
                            else
                                element.style.display = "block";
                        }

                    }
                </script>
                <?php

                //Get the comment number count
                $comments_check = mysqli_query($this->con, "SELECT * FROM comments WHERE post_id='$id'");
                $comments_check_num = mysqli_num_rows($comments_check);

                //Timeframe
                $date_time_now = date("Y-m-d H:i:s");
                $start_date = new DateTime($date_time); //Time of post
                $end_date = new DateTime($date_time_now); //Current time
                $interval = $start_date->diff($end_date); //Difference between dates

                if ($interval->y >= 1) {
                    if ($interval->y == 1) {
                        $time_message = $interval->y . " year ago"; //1 year ago
                    } else {
                        $time_message = $interval->y . " years ago"; //.. years ago
                    }
                } else if ($interval->m >= 1) {
                    if ($interval->d == 0) {
                        $days = " ago";
                    } else if ($interval->d == 1) {
                        $days = $interval->d . " day ago";
                    } else {
                        $days = $interval->d . " days ago";
                    }

                    if ($interval->m == 1) {
                        $time_message = $interval->m . " month " . $days;
                    } else {
                        $time_message = $interval->m . " months " . $days;
                    }
                } else if ($interval->d >= 1) {
                    if ($interval->d == 1) {
                        $time_message = "Yesterday";
                    } else {
                        $time_message = $interval->d . " days ago";
                    }
                } else if ($interval->h >= 1) {
                    if ($interval->h == 1) {
                        $time_message = $interval->h . " hour ago";
                    } else {
                        $time_message = $interval->h . " hours ago";
                    }
                } else if ($interval->i >= 1) {
                    if ($interval->i == 1) {
                        $time_message = $interval->i . " minute ago";
                    } else {
                        $time_message = $interval->i . " minutes ago";
                    }
                } else {
                    if ($interval->s < 30) {
                        $time_message = "Just now";
                    } else {
                        $time_message = $interval->s . " seconds ago";
                    }
                }

                //With each iteration, add a post to the html
                $html .= "<div class='status_post' onClick='javascript:toggle$id(event)'>
                                <div class='post_profile_pic'>
                                    <img src='$profile_pic' width='50'>
                                </div>

                                <div class='posted_by' style='color: #ACACAC;'>
                                    <a href='$added_by'>$first_name $last_name </a> &nbsp;&nbsp;&nbsp;$time_message
                                    $delete_button
                                </div>
                                <div id='post_body'>
                                    $body
                                    <br>
                                    <br>
                                    <br>
                                </div>

                                <div class='newsfeedPostOptions'>
                                    Comments($comments_check_num)&nbsp;&nbsp;&nbsp;
                                    <iframe src='like.php?post_id=$id' scrolling='no'></iframe>
                                </div>
                            </div>
                            <div class='post_comment' id='toggleComment$id' style='display:none;'>
                                <iframe src='comment_frame.php?post_id=$id' id='comment_iframe' frameborder='0'></iframe>
                            </div>
                            <hr>";

                ?>
                <script>
                    /* var deletePost = document.querySelector('#post<?php echo $id; ?>');
                    deletePost.addEventListener("click", function() {
                        alert("test <?php //echo $id; 
                                    ?>");
                    }); */
                    $(document).ready(function() {
                        $('#post<?php echo $id; ?>').on('click', function() {
                            // Comes with bootstrap JS
                            bootbox.confirm("Are you sure you want to delete this post?", function(result) {
                                $.post("includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>", {
                                    result: result
                                });

                                if (result) { //if result is true/exists
                                    location.reload()
                                }
                            });
                        });
                    });
                </script>
                <?php

            } //END WHILE LOOP

            //If there is some posts still left after 10 have been loaded - for ajax 
            if ($count > $limit) {
                //Increase the "page" size with a hidden page number, so ajax would load the next posts
                $html .= "<input type='hidden' class='nextPage' value='" . ($page + 1) . "'> 
                          <input type='hidden' class='noMorePosts' value='false'>"; //Hidden input to keep track of a value
            } else {
                if ($num_iterations == 0) {
                    $html .= "<input type='hidden' class='noMorePosts' value='true'><p style='text-align:center;'>No posts to show!</p>"; //Hidden input to keep track 
                } else {
                    //If there are no more posts to show, let the user know
                    $html .= "<input type='hidden' class='noMorePosts' value='true'><p style='text-align:center;'>No more posts to show!</p>"; //Hidden input to keep track of a value
                }
            }
        }
        //When the loop is done, echo the html
        echo $html;
    }

    public function getSinglePost($post_id) {
        $userLoggedIn = $this->user_obj->getUsername();

        $opened_query = mysqli_query($this->con, "UPDATE notifications SET opened='yes' WHERE user_to='$userLoggedIn' AND link LIKE '%=$post_id'");

        $html = ""; //HTml to return in the end
        $data_query = mysqli_query($this->con, "SELECT * FROM posts WHERE deleted='no' AND id='$post_id'"); //Latest first

        if (mysqli_num_rows($data_query) > 0) { //If at least one row is sent back from db

                $row = mysqli_fetch_array($data_query);      
                $id = $row['id'];
                $body = $row['body'];
                $added_by = $row['added_by'];
                $date_time = $row['date_added'];

                //Prepare user_to string so it cn be included even if not posted to a user
                if ($row['user_to'] == "none") {
                    $user_to = "";
                } else {
                    $user_to_obj = new User($this->con, $row['user_to']); //New instance of the user to object (To post on someones page!!)
                    $user_to_name = $user_to_obj->getFirstAndLastName();
                    $user_to = "to <a href='" . $row['user_to'] . "'>" . $user_to_name . "</a>";
                }

                //Check if user who posted, has their account closed
                $added_by_obj = new User($this->con, $added_by);
                if ($added_by_obj->isClosed()) {
                    return; //go to the next iteration of the loop, if user account is closed
                }

                //To show only friends posts on feed!!
                $user_logged_obj = new User($this->con, $userLoggedIn);

                if ($user_logged_obj->isFriend($added_by)) {

                    // Delete post button
                    if ($userLoggedIn == $added_by)
                        $delete_button = "<button class='delete_button btn-danger' id='post$id'>X</button>";
                    else
                        $delete_button = "";

                    $user_details_query = mysqli_query($this->con, "SELECT first_name, last_name, profile_pic FROM users WHERE username='$added_by'");
                    $user_row = mysqli_fetch_array($user_details_query);

                    $first_name = $user_row['first_name'];
                    $last_name = $user_row['last_name'];
                    $profile_pic = $user_row['profile_pic'];

                ?>
                    <!-- COMMENTS BLOCK TOGGLE FUNCTION -->
                    <script>
                        function toggle<?php echo $id; ?>(event) {

                            var target = $(event.target);

                            if (!target.is('a') && !target.is('button')) {
                                var element = document.getElementById("toggleComment<?php echo $id; ?>");

                                if (element.style.display == "block")
                                    element.style.display = "none";
                                else
                                    element.style.display = "block";
                            }

                        }
                    </script>
                <?php

                    //Get the comment number count
                    $comments_check = mysqli_query($this->con, "SELECT * FROM comments WHERE post_id='$id'");
                    $comments_check_num = mysqli_num_rows($comments_check);

                    //Timeframe
                    $date_time_now = date("Y-m-d H:i:s");
                    $start_date = new DateTime($date_time); //Time of post
                    $end_date = new DateTime($date_time_now); //Current time
                    $interval = $start_date->diff($end_date); //Difference between dates

                    if ($interval->y >= 1) {
                        if ($interval->y == 1) {
                            $time_message = $interval->y . " year ago"; //1 year ago
                        } else {
                            $time_message = $interval->y . " years ago"; //.. years ago
                        }
                    } else if ($interval->m >= 1) {
                        if ($interval->d == 0) {
                            $days = " ago";
                        } else if ($interval->d == 1) {
                            $days = $interval->d . " day ago";
                        } else {
                            $days = $interval->d . " days ago";
                        }

                        if ($interval->m == 1) {
                            $time_message = $interval->m . " month " . $days;
                        } else {
                            $time_message = $interval->m . " months " . $days;
                        }
                    } else if ($interval->d >= 1) {
                        if ($interval->d == 1) {
                            $time_message = "Yesterday";
                        } else {
                            $time_message = $interval->d . " days ago";
                        }
                    } else if ($interval->h >= 1) {
                        if ($interval->h == 1) {
                            $time_message = $interval->h . " hour ago";
                        } else {
                            $time_message = $interval->h . " hours ago";
                        }
                    } else if ($interval->i >= 1) {
                        if ($interval->i == 1) {
                            $time_message = $interval->i . " minute ago";
                        } else {
                            $time_message = $interval->i . " minutes ago";
                        }
                    } else {
                        if ($interval->s < 30) {
                            $time_message = "Just now";
                        } else {
                            $time_message = $interval->s . " seconds ago";
                        }
                    }

                    //With each iteration, add a post to the html
                    $html .= "<div class='status_post' onClick='javascript:toggle$id(event)'>
                                <div class='post_profile_pic'>
                                    <img src='$profile_pic' width='50'>
                                </div>

                                <div class='posted_by' style='color: #ACACAC;'>
                                    <a href='$added_by'>$first_name $last_name </a> $user_to &nbsp;&nbsp;&nbsp;$time_message
                                    $delete_button
                                </div>
                                <div id='post_body'>
                                    $body
                                    <br>
                                    <br>
                                    <br>
                                </div>

                                <div class='newsfeedPostOptions'>
                                    Comments($comments_check_num)&nbsp;&nbsp;&nbsp;
                                    <iframe src='like.php?post_id=$id' scrolling='no'></iframe>
                                </div>
                            </div>
                            <div class='post_comment' id='toggleComment$id' style='display:none;'>
                                <iframe src='comment_frame.php?post_id=$id' id='comment_iframe' frameborder='0'></iframe>
                            </div>
                            <hr>";
                
                ?>
                <script>
                    /* var deletePost = document.querySelector('#post<?php echo $id; ?>');
                    deletePost.addEventListener("click", function() {
                        alert("test <?php //echo $id; 
                                    ?>");
                    }); */
                    $(document).ready(function() {
                        $('#post<?php echo $id; ?>').on('click', function() {
                            // Comes with bootstrap JS
                            bootbox.confirm("Are you sure you want to delete this post?", function(result) {
                                $.post("includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>", {
                                    result: result
                                });

                                if (result) { //if result is true/exists
                                    location.reload()
                                }
                            });
                        });
                    });
                </script>
                <?php
                } else { //If not friends with the post author
                    echo "<p>You cannot see this post because you are not friends with this user.</p>";
                    return;
                }
        }
        else { //When trying to access nonexisting post id
            echo "<p>No post found. If you clicked on a post, it might be deleted, or the link is broken.</p>";
            return;
        }
        //When the loop is done, echo the html
        echo $html;
    }
}
