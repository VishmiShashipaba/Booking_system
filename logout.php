<?php
// logout.php
// Clears session variables and logs the user out

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/db.php';

// Unset all session values
$_SESSION = array();

// If session was cookie-based, expire the cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Redirect to Homepage
header("Location: " . BASE_URL . "index.php");
exit();
?>
