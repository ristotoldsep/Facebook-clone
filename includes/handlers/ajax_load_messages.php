<?php 
include("../../config/config.php"); //DB
include("../classes/User.php");
include("../classes/Message.php");

$limit = 6;//Number of messages to load

// request comes from ajax request in facebook.js, function get DropdownData
$message = new Message($con, $_REQUEST['userLoggedIn']); //Request includes pagename and userLoggedIn data
echo $message->getConvosDropdown($_REQUEST, $limit);

?>