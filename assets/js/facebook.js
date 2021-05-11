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


// Make search input larger on bigger screens when clicked/focused on
$('#search_text_input').focus(function() {
    console.log("done");
    
    if (window.matchMedia( "(min-width: 800px)" ).matches) {
        $(this).animate({width: '300px'}, 500);
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
