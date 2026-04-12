<?php
session_start();

// 1. Clear all session variables
$_SESSION = [];

// 2. If a session cookie exists, delete it
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Destroy the session on the server
session_destroy();

// 4. Delete the "Remember Me" cookie
// We set the expiration time to one hour ago (time() - 3600)
if (isset($_COOKIE['remember_user'])) {
    setcookie("remember_user", "", time() - 3600, "/");
}

// 5. Redirect to the login page
header("Location: login.php");
exit;
?>