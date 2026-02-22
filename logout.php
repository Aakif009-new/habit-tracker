<?php
// logout.php
// Subject: Web Technology - Session Management

session_start();

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Clear remember me cookie
setcookie('remember_user', '', time() - 3600, '/');

// Redirect to login page
header("Location: login.php?message=logged_out");
exit();
?>