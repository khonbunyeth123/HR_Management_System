<?php
session_start();

include(__DIR__ . "/../../action/db/cn.php");
include(__DIR__ . "/../../utils/response.php");
$cn->set_charset("utf8");
if ($cn->connect_error) {
    jsonResponse(false, 'Database connection failed', [], 500);
}

// Check if user is logged in
if (isset($_SESSION['login']) && $_SESSION['login'] === true) {

    $uuid = $_SESSION['uuid'] ?? null;

    if ($uuid) {
        // Clear login_session in DB
        $stmt = $cn->prepare("UPDATE tbl_users SET login_session = NULL, updated_at = NOW() WHERE uuid = ?");
        $stmt->bind_param("s", $uuid);
        $stmt->execute();
    }

    // Destroy all session data
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    session_destroy();

    jsonResponse(true, 'Logout successful');
} else {
    jsonResponse(false, 'No active session');
}
