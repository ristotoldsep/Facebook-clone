<?php
//Objecto oriented PHP - User class
class Post {
    //Will only be able to use these variables inside this class
    private $user_obj;
    private $con;

    //Constructor (like in react) - creates the user object upon call = $new_obj = new User($con, $userLoggedIn) ...
    public function __construct($con, $user) {
        //"THIS" REFERENCES THE CLASS OBJECT
        $this->con = $con;
        $this->user_obj = new User($con, $user); //With each post, create a new instance of User class
    }

    public function submitPost($body, $user_to) {
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

            //If user is not on own profile, user_to is 'none'
            if ($user_to == $added_by) {
                $user_to = "none";
            }

            //Insert post to db
            $query = mysqli_query($this->con, "INSERT INTO posts VALUES ('', '$body', '$added_by', '$user_to', '$date_added', 'no', 'no', '0')");

            $returned_id = mysqli_insert_id($this->con); //Returns the id of the post submitted

            //Insert notification

            //Update post count for user
            $num_posts = $this->user_obj->getNumPosts(); //Return the number of posts
            $num_posts++; //Increment the post count for the user
            $update_query = mysqli_query($this->con, "UPDATE posts SET num_posts='$num_posts' WHERE username='$added_by'"); //Update the user 
        }
    }

}
