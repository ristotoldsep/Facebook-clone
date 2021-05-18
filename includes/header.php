<?php require 'config/config.php'; //getting $con var
include("includes/classes/User.php"); //Call in the USER CLASS
include("includes/classes/Post.php"); //Call in the Post CLASS
include("includes/classes/Message.php"); //Call in the Message CLASS
include("includes/classes/Notification.php"); //Call in the Notification CLASS

//If user is logged in 
if (isset($_SESSION['username'])) {
    $userLoggedIn = $_SESSION['username'];

    //Get user details from db
    $user_details_query = mysqli_query($con, "SELECT * FROM users WHERE username='$userLoggedIn'");

    $user = mysqli_fetch_array($user_details_query); //return array from db (info about the logged in user)

} else {
    header("Location: register.php"); //If not logged in, redirect to register
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="https://cdn1.iconfinder.com/data/icons/logotypes/32/square-facebook-512.png">
    <title>Facebook</title>

    <!-- Javascript -->

    <!-- jquery js -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <!-- Bootstrap js -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script src="assets/js/bootbox.min.js"></script>
    <script src="assets/js/facebook.js"></script>
    <script src="assets/js/jquery.Jcrop.js"></script>
    <script src="assets/js/jcrop_bits.js"></script>

    <!-- CSS -->

    <!-- Font awesome -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">
    <!-- My CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/jquery.Jcrop.css" type="text/css" />
</head>

<body>

    <div class="top_bar">
        <div class="logo">
            <a href="index.php">Facebook</a>
        </div>

        <!-- SEARCH FORM -->
        <div class="search">

            <form action="search.php" method="GET" name="search_form">
                <!-- AJAX request in facebook.js -->
                <input type="text" onkeyup="getLiveSearchUsers(this.value, '<?php echo $userLoggedIn; ?>')" name="q" placeholder="Search..." autocomplete="off" id="search_text_input">

                <div class="button_holder">
                    <img src="assets/images/icons/magnifier.png" alt="">
                </div>
            </form>


            <div class="search_results"></div>

            <div class="search_results_footer_empty"></div>

        </div>

        <nav>
            <?php
            //Unrrad messages
            $messages = new Message($con, $userLoggedIn);
            $num_messages = $messages->getUnreadNumber();

            //Unrrad notifications
            $notifications = new Notification($con, $userLoggedIn);
            $num_notifications = $notifications->getUnreadNumber();

            //Friend requests number
            $user_obj = new User($con, $userLoggedIn);
            $num_requests = $user_obj->getNumberOfFriendRequests();
            ?>

            <a href="<?php echo $userLoggedIn; ?>">
                <?php
                echo $user['username'];
                ?>
            </a>
            <a href="index.php">
                <i class="fa fa-home fa-lg" aria-hidden="true"></i>
            </a>
            <!-- Open Messages Dropdown for the logged in user, type = message -->
            <a href="javascript:void(0);" onclick="getDropDownData('<?php echo $userLoggedIn; ?>', 'message')">
                <i class="fa fa-envelope fa-lg" aria-hidden="true"></i>
                <?php
                if ($num_messages > 0)
                    echo '<span class="notification_badge" id="unread_message">' . $num_messages . '</span>';
                ?>

            </a>
            <!-- Notifications dropdown -->
            <a href="javascript:void(0);" onclick="getDropDownData('<?php echo $userLoggedIn; ?>', 'notification')">
                <i class="fa fa-bell-o fa-lg" aria-hidden="true"></i>
                <?php
                if ($num_notifications > 0)
                    echo '<span class="notification_badge" id="unread_notification">' . $num_notifications . '</span>';
                ?>
            </a>
            <a href="requests.php">
                <i class="fa fa-users fa-lg" aria-hidden="true"></i>
                <?php
                if ($num_requests > 0)
                    echo '<span class="notification_badge" id="unread_requests">' . $num_requests . '</span>';
                ?>
            </a>
            <a href="settings.php">
                <i class="fa fa-cog fa-lg" aria-hidden="true"></i>
            </a>
            <a href="includes/handlers/logout.php">
                <i class="fa fa-sign-out fa-lg" aria-hidden="true"></i>
            </a>
        </nav>

        <div class="dropdown_data_window" style="height: 0px; border: none;">

        </div>
        <input type="hidden" name="" id="dropdown_data_type" value="">

    </div>

    <!-- INFINITE SCROLLING -->
    <!-- <script>
        $(function() {

            var userLoggedIn = '<?php echo $userLoggedIn; ?>';
            var dropdownInProgress = false;

            $(".dropdown_data_window").scroll(function() {
                var bottomElement = $(".dropdown_data_window a").last();
                var noMoreData = $('.dropdown_data_window').find('.noMoreDropdownData').val();

                // isElementInViewport uses getBoundingClientRect(), which requires the HTML DOM object, not the jQuery object. The jQuery equivalent is using [0] as shown below.
                if (isElementInView(bottomElement[0]) && noMoreData == 'false') {
                    loadPosts();
                }
            });

            function loadPosts() {
                if (dropdownInProgress) { //If it is already in the process of loading some posts, just return
                    return;
                }

                dropdownInProgress = true;

                var page = $('.dropdown_data_window').find('.nextPageDropdownData').val() || 1; //If .nextPage couldn't be found, it must not be on the page yet (it must be the first time loading posts), so use the value '1'

                var pageName; //Holds name of page to send ajax request to
                var type = $('#dropdown_data_type').val();

                if (type == 'notification')
                    pageName = "ajax_load_notifications.php";
                else if (type == 'message')
                    pageName = "ajax_load_messages.php";

                $.ajax({
                    url: "includes/handlers/" + pageName,
                    type: "POST",
                    data: "page=" + page + "&userLoggedIn=" + userLoggedIn,
                    cache: false,

                    success: function(response) {

                        $('.dropdown_data_window').find('.nextPageDropdownData').remove(); //Removes current .nextpage 
                        $('.dropdown_data_window').find('.noMoreDropdownData').remove();

                        $('.dropdown_data_window').append(response);

                        dropdownInProgress = false;
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
    </script> -->

    <script>
        //Username of the logged in user
        let userLoggedIn = '<?php echo $userLoggedIn; ?>';

        $(document).ready(function() {

            $('.dropdown_data_window').scroll(function() {
                let inner_height = $('.dropdown_data_window').innerHeight(); //div containing messages
                let scroll_top = $('.dropdown_data_window').scrollTop();
                let page = $('.dropdown_data_window').find('.nextPageDropdownData').val();
                let noMoreData = $('.dropdown_data_window').find('.noMoreDropdownData').val();

                //When user scrolls and more posts have to be loaded
                if ((scroll_top + inner_height >= $('.dropdown_data_window')[0].scrollHeight) && noMoreData == 'false') {

                    var pageName; //Holds name of page to send ajax request to
                    var type = $('#dropdown_data_type').val();

                    if (type == 'notification')
                        pageName = "ajax_load_notifications.php";
                    else if (type == 'message')
                        pageName = "ajax_load_messages.php";


                    let ajaxReq = $.ajax({
                        url: "includes/handlers/" + pageName,
                        type: "POST",
                        data: "page=" + page + "&userLoggedIn=" + userLoggedIn,
                        cache: false,

                        success: function(response) {
                            $('.dropdown_data_window').find('.nextPageDropdownData').remove(); //Removes current .nextPage
                            $('.dropdown_data_window').find('.noMoreDropdownData').remove(); //Removes current .nextPage

                            $('.dropdown_data_window').append(response);
                        }
                    });
                } //End if

                return false; //If there are no more posts

            }); //End  $(window).scroll(function())
        });
    </script>

    <div class="wrapper">