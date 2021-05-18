$(document).ready(function() {
    //Button for profile post
    $('#submit_profile_post').click(function() {
        $.ajax({
            type: "POST",
            url: "includes/handlers/ajax_submit_profile_post.php",
            data: $('form.profile_post').serialize(),
            success: function(msg) {
                $('#post_form').modal('hide');
                location.reload();
            },
            error: function () {
                alert("Failed to post!");
            }
        });
    });
});

function getUsers(value, user) {
    //Make ajax request
    $.post("includes/handlers/ajax_friend_search.php", {query: value, userLoggedIn: user}, function(data) {

        //Append the returned data to results div
        $(".results").html(data);
        
    });
}

function getDropDownData(user, type) {
    // If the dropdown is currently closed
    if ($(".dropdown_data_window").css("height") == "0px") {

        var pageName; /* Which page we are sending the ajax request */

        if(type == "notification") {
            // Notification type
            pageName = "ajax_load_notifications.php";
            $("span").remove("#unread_notification");
        }
        else if (type == "message") {
            pageName = "ajax_load_messages.php";
            $("span").remove("#unread_message");
        }

        var ajaxReq = $.ajax({
            url: "includes/handlers/" + pageName,
            type: "POST",
            data: "page=1&userLoggedIn=" + user,
            cache: false,
            
            // Append the messages to the div
            success: function(response) {
                $(".dropdown_data_window").html(response);
                $(".dropdown_data_window").css({"padding" : "0px", "height" : "280px", "border" : "1px solid #DADADA"});
                $("#dropdown_data_type").val(type);
            }
        });
    }
    // IF header dropdown div is already open
    else {
        $(".dropdown_data_window").html("");
        $(".dropdown_data_window").css({ "padding": "0px", "height": "0px", "border": "none"});
    }
}


// Make search input larger on bigger screens when clicked/focused on
$('#search_text_input').focus(function() {
    console.log("done");
    
    if (window.matchMedia( "(min-width: 800px)" ).matches) {
        $(this).animate({width: '500px'}, 500);
    }
    
});

//Submit button
$('.button_holder').on("click", function() {
    document.search_form.submit()
});

// Show search results screen and post ajax request!
function getLiveSearchUsers(value, user) {
    //Ajax request for the users
    $.post("includes/handlers/ajax_search.php", {query:value, userLoggedIn: user}, function(data) {

        if($(".search_results_footer_empty")[0]) {
            $(".search_results_footer_empty").toggleClass("search_results_footer"); //Add/Remove the search suggestions box
            $(".search_results_footer_empty").toggleClass("search_results_footer_empty");
        }

        $(".search_results").html(data);
        $(".search_results_footer").html("<a href='search.php?q=" + value + "'>See All Results</a>");

        if (data == "") {
            $(".search_results_footer").html("");
            $(".search_results_footer").toggleClass("search_results_footer_empty");
            $(".search_results_footer").toggleClass("search_results_footer");
        }
    });
}

// Hide the search when clicked elsewhere
$(document).click(function (e) {
    // Search results box
    if (e.target.class != "search_results" && e.target.id != "search_text_input") {

        $(".search_results").html(""); //Empty the html
        $(".search_results_footer").html("");
        $(".search_results_footer").toggleClass("search_results_footer_empty");
        $(".search_results_footer").toggleClass("search_results_footer");
    }

    if (e.target.class != "dropdown_data_window") {

        $(".dropdown_data_window").html(""); //Empty the html
        $(".dropdown_data_window").css({"padding": "0px", "height": "0px"}); //Empty the css from notification dropdown
    }

});
