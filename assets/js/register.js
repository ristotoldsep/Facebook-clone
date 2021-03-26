$(document).ready(function() {
    //On click signup, hide login and show registration form
    $("#signup").click(function() {
        //First close login and then open register form
        $("#first").slideUp("slow", function() {
            $("#second").slideDown("slow");
        });
    });
    //On click sigin, hide register and show login form
    $("#signin").click(function() {
        //First close login and then open register form
        $("#second").slideUp("slow", function() {
            $("#first").slideDown("slow");
        });
    });
});
