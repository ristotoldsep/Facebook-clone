<?php 
include("../../config/config.php");
include("../classes/User.php");

//These come from facebook.js AJAX REQUEST, and onkeyup form in messages.php fires the JS
$query = $_POST['query'];
$userLoggedIn = $_POST['userLoggedIn'];

$names = explode(" ", $query); //Split the array with spaces

//If _ is used in the query, search for usernames
if (strpos($query, "_") !== false) {
    $usersReturned = mysqli_query($con, "SELECT * FROM users WHERE username LIKE '$query%' AND user_closed='no' LIMIT 8");
}
else if (count($names) >= 2 && count($names) < 4) {
    $usersReturned = mysqli_query($con, "SELECT * FROM users WHERE (first_name LIKE '$names[0]%' AND last_name LIKE '$names[1]%') AND user_closed='no' LIMIT 8");
}
else {
    $usersReturned = mysqli_query($con, "SELECT * FROM users WHERE (first_name LIKE '$names[0]%' OR last_name LIKE '$names[0]%') AND user_closed='no' LIMIT 8");
}

if ($query != "") {
    while ($row = mysqli_fetch_array($usersReturned)) {

        $user = new User($con, $userLoggedIn);

        if ($row['username'] != $userLoggedIn) {
            $mutual_friends = $user->getMutualFriends($row['username']) . " friends in common";
        }
        else {
            $mutual_friends = "";
        }

        //Everytime a user is found, create this data html, to send back to ajax 
        if ($user->isFriend($row['username'])) {
            echo "<div class='resultDisplay'>
                <a href='messages.php?u=" . $row['username'] . "' style='color:#000;'>
                    <div class='liveSearchProfilePic'>
                        <img src='" . $row['profile_pic'] . "'>
                    </div>

                    <div class='liveSearchText'>
                        ". $row['first_name'] . " " . $row['last_name'] . "
                        <p style='margin: 0;'>" . $row['username'] . "</p>
                        <p id='grey'>" . $mutual_friends . "</p>
                    </div>
                </a>
            </div>";
        }
    }
}

?>