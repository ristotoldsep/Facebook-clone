<?php
include "../../config/config.php";

$id = $_POST['id'];
$delete_query = mysqli_query($con, "DELETE FROM friend_requests WHERE id='$id'");
?>