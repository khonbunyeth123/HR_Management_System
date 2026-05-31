<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Load .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Database connection
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Core/Database.php';

function jsonResponse($success, $message, $code = 200) {
    header('Content-Type: application/json');
    http_response_code($code);
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

try {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $token = isset($_POST['token']) ? trim($_POST['token']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($email) || empty($token) || empty($password)) {
        jsonResponse(false, 'All fields are required', 400);
    }

    if (strlen($password) < 8) {
        jsonResponse(false, 'Password must be at least 8 characters long', 400);
    }

    $db = \App\Core\Database::getInstance();

    // Verify token
    $resetQuery = "SELECT created_at FROM password_resets WHERE email = ? AND token = ? LIMIT 1";
    $reset = $db->query($resetQuery, [$email, $token]);

    if (empty($reset)) {
        jsonResponse(false, 'Invalid or expired reset link.', 400);
    }

    // Check expiration (1 hour)
    $createdAt = strtotime($reset[0]['created_at']);
    if (time() - $createdAt > 3600) {
        $db->query("DELETE FROM password_resets WHERE email = ?", [$email]);
        jsonResponse(false, 'Reset link has expired.', 400);
    }

    // Update password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
    // Update tbl_users
    $db->query("UPDATE tbl_users SET password = ?, updated_at = NOW() WHERE email = ?", [$hashedPassword, $email]);
    
    // Update tbl_employees
    $db->query("UPDATE tbl_employees SET password = ?, updated_at = NOW() WHERE email = ?", [$hashedPassword, $email]);

    // Delete token
    $db->query("DELETE FROM password_resets WHERE email = ?", [$email]);

    jsonResponse(true, 'Password has been reset successfully.');

} catch (\Exception $e) {
    error_log("Reset password error: " . $e->getMessage());
    jsonResponse(false, 'An error occurred. Please try again later.', 500);
}
?>
