<?php
//Objectoriented PHP - Message class
class Notification
{
    //Will only be able to use these variables inside this class
    private $user_obj;
    private $con;

    //Constructor (like in react) - creates the user object upon call = $new_obj = new User($con, $userLoggedIn) ...
    public function __construct($con, $user) {
        //"THIS" REFERENCES THE CLASS OBJECT
        $this->con = $con;
        $this->user_obj = new User($con, $user); //With each post, create a new instance of User class
    }

    public function getUnreadNumber() {
        // Return the number of unread messages for the user!
        $userLoggedIn = $this->user_obj->getUsername();

        $query = mysqli_query($this->con, "SELECT * FROM notifications WHERE viewed='no' AND user_to='$userLoggedIn'");

        return mysqli_num_rows($query);
    }

    public function getNotifications($data, $limit) {
        $page = $data['page'];
        $userLoggedIn = $this->user_obj->getUsername();
        $return_string = "";

        if ($page == 1)
            $start = 0; //Start from the first post
        else
            $start = ($page - 1) * $limit; //For infinite scrolling, to load new objects from the last position

        $set_viewed_query = mysqli_query($this->con, "UPDATE notifications SET viewed='yes' WHERE user_to='$userLoggedIn'");

        //Most recent one at the top
        $query = mysqli_query($this->con, "SELECT * FROM notifications WHERE user_to='$userLoggedIn' ORDER BY id DESC");

        if (mysqli_num_rows($query) == 0) {
            echo "<p class='nomoremessages' style='text-align:center;'>You have no notifications!</p>";
            return;
        }

        $num_iterations = 0; //Number of messages checked
        $count = 1; //Number of messages posted

        while ($row = mysqli_fetch_array($query)) {

            // 2 ways of doing if else when incrementing
            if ($num_iterations++ < $start)
                continue; // if it doesn't reach a start point yet, continue

            if ($count > $limit)
                break; //if we've reached our limit of how many messages to load, we want to break the loop
            else
                $count++;

            $user_from = $row['user_from'];

            $user_data_query = mysqli_query($this->con, "SELECT * FROM users WHERE username='$user_from'");
            $user_data = mysqli_fetch_array($user_data_query); //Get the user that the notification is from

            //Timeframe
            $date_time_now = date("Y-m-d H:i:s");
            $start_date = new DateTime($row['datetime']); //Time of post
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

            $opened = $row['opened'];

            $bgstyle = (isset($row['opened']) && $row['opened'] == 'no') ? "background-color: #DDEDFF;" : ""; //If message unread, gray bg-color, ADDED ISSET TO FIRST CHECK THIS VALUE EXISTS

            $pstyle = (isset($row['opened']) && $row['opened'] == 'no') ? "color: #000; font-weight: 500" : ""; //If message unread, gray bg-color, ADDED ISSET TO FIRST CHECK THIS VALUE EXISTS

            $return_string .= "<a href='" . $row['link'] . "'>
                                    <div class='resultDisplay resultDisplayNotification' style='" . $bgstyle ."'>
                                        <div class='notificationsProfilePic'>
                                            <img src='" . $user_data['profile_pic'] . "'>
                                        </div>
                                        <p class='timestamp_smaller' id='grey'>" . $time_message . "</p>" . $row['message'] . "
                                    </div>
                                </a>";
        }

        // IF all notifications were loaded
        if ($count > $limit) {
            $return_string .= "<input type='hidden' class='nextPageDropdownData' value='" . ($page + 1) . "'>
                                <input type='hidden' class='noMoreDropdownData' value='false'>";
        } else {
            $return_string .= "<input type='hidden' class='noMoreDropdownData' value='true'><p class='nomoremessages' style='text-align:center;'>No more notifications to load!</p>";
        }

        return $return_string;
    }

    public function insertNotification($post_id, $user_to, $type) {

        $userLoggedIn = $this->user_obj->getUsername();
        $userLoggedInName = $this->user_obj->getFirstAndLastName();

        $date_time = date("Y-m-d H:i:s");

        switch($type) {
            case 'comment':
                $message = $userLoggedInName . " commented on your post";
                break;
            case 'like':
                $message = $userLoggedInName . " liked your post";
                break;
            case 'profile_post':
                $message = $userLoggedInName . " posted on your profile";
                break;
            case 'comment_non_owner':
                $message = $userLoggedInName . " commented on a post you commented on";
                break;
            case 'profile_comment':
                $message = $userLoggedInName . " commented on your profile post";
                break;
        }

        $link = "post.php?id=" . $post_id;
        
        $insert_query = mysqli_query($this->con, "INSERT INTO notifications VALUES('', '$user_to', '$userLoggedIn', '$message', '$link', '$date_time', 'no', 'no')");
    }
}
?>