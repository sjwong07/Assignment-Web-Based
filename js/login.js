$(document).ready(function() {
    $('#login-form').on('submit', function(e) {
        if ($('#username').val() === "") {
            alert("Username is required");
            e.preventDefault();
        }
    });
});