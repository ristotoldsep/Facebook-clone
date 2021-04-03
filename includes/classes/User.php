<?php
//Object oriented PHP - User class
class User {
    //Will only be able to use these variables inside this class
    private $user; //Basically equals this->user
    private $con;

    //Constructor (like in react) - creates the user object upon call = $new_obj = new User($con, $userLoggedIn) ...
    public function __construct($con, $user) {
        //"THIS" REFERENCES THE CLASS OBJECT
        $this->con = $con;
        $user_details_query = mysqli_query($con, "SELECT * FROM users WHERE username='$user'");
        $this->user = mysqli_fetch_array($user_details_query);
    }

    public function getUsername() {
        return $this->user['username']; //Used in Post.php class for example
    }

    public function getNumPosts() {
        $username = $this->user['username'];
        $query = mysqli_query($this->con, "SELECT num_posts FROM users WHERE username='$username'"); 
        $row = mysqli_fetch_array($query);
        return $row['num_posts'];
    }

    public function getFirstAndLastName() {
        $username = $this->user['username'];
        $query = mysqli_query($this->con, "SELECT first_name, last_name FROM users WHERE username='$username'");
        $row = mysqli_fetch_array($query);
        return $row['first_name'] . " " . $row['last_name'];
    }

    public function isClosed() {
        $username = $this->user['username'];
        $query = mysqli_query($this->con, "SELECT user_closed FROM users WHERE username='$username'");
        $row = mysqli_fetch_array($query);

        if ($row['user_closed'] == "yes")
            return true;
        else
            return false;
    }

}


?>