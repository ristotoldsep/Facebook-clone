<?php 
include("../../config/config.php"); //Db connection
include("../../includes/classes/User.php"); //Db connection

//Passed from js as parameters (demo.js)
$query = $_POST['query'];
$userLoggedIn = $_POST['userLoggedIn'];

$names = explode(" ", $query); //Explode => search query = risto tõldsep => result = [0] risto [1] tõldsep  = Creates an array!

// DB QUERIES FROM SEARCH INPUT
//If query contains an underscore, assume user is searching for usernames  
if (strpos($query, '_') !== false) {
    $usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE username LIKE '$query%' AND user_closed='no' LIMIT 8");
}
//If there are two words, assume they are first and last names, search for either one
else if (count($names) == 2) {
    $usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE (first_name LIKE '$names[0]%' OR last_name LIKE '$names[1]%') AND user_closed='no' LIMIT 8");
}
//If query has only one word, search all names
else {
    $usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE (first_name LIKE '$names[0]%' OR last_name LIKE '$names[0]%') AND user_closed='no' LIMIT 8");
}

// If query is not empty
if ($query != "") {

    while($row = mysqli_fetch_array($usersReturnedQuery)) {
        $user = new User($con, $userLoggedIn); //get all the user details + extra methods

        if ($row['username'] != $userLoggedIn) {
            $mutual_friends = $user->getMutualFriends($row['username']) . " friends in common";
        } else {
            $mutual_friends = "";
        }

        // $mutual_friends = "risto!"; //Delete when method is made

        // Echo out the search result html
        echo "
            <div class='resultDisplay'>
                <a href='" . $row['username'] . "' style='color: #1485BD;'>
                    <div class='liveSearchProfilePic'>
                        <img src='" . $row['profile_pic'] . "'>
                    </div>

                    <div class='liveSearchText'>
                        " . $row['first_name'] . " " . $row['last_name'] . "
                        <p style='margin:0;'>" . $row['username'] . "</p>
                        <p id='grey'>" . $mutual_friends . "</p>
                    </div>
                </a>
            </div>
        ";
    }

}


?>