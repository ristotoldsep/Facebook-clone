<?php require '../../config/config.php'; //getting $con var
include("../classes/User.php"); //Call in the USER CLASS
include("../classes/Post.php"); //Call in the Post CLASS
include("../classes/Notification.php"); //Call in the Post CLASS

if (isset($_POST['post_body'])) {
    $post = new Post($con, $_POST['user_from']);
    $post->submitPost($_POST['post_body'], $_POST['user_to'], ""); 
}

?>