<?php
session_start();
// Load .env file
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Database connection
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Core/Database.php';

// Response helper
function jsonResponse($success, $message, $data = [], $code = 200) {
    header('Content-Type: application/json');
    http_response_code($code);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'dpl' => $success // For compatibility with your frontend
    ]);
    exit;
}

try {
    // Get POST data
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $remember = isset($_POST['remember']) ? (bool)$_POST['remember'] : false;

    // Validate input
    if (empty($email) || empty($password)) {
        jsonResponse(false, 'Email and password are required', [], 400);
    }

    // Get database connection
    $db = \App\Core\Database::getInstance();
    
    // Query user by email
    $query = "SELECT id, uuid, username, full_name, email, password, role_id, status_id FROM tbl_users WHERE email = ? LIMIT 1";
    $result = $db->query($query, [$email]);

    if (empty($result)) {
        jsonResponse(false, 'Invalid email or password', [], 401);
    }

    $user = $result[0];

    // Check if user is active
    if ($user['status_id'] != 1) {
        jsonResponse(false, 'Your account has been disabled', [], 403);
    }

    // Verify password
    if (!password_verify($password, $user['password'])) {
        jsonResponse(false, 'Invalid email or password', [], 401);
    }

    // Set session variables
    $_SESSION['login'] = true;
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['uuid'] = $user['uuid'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role_id'] = (int)$user['role_id'];

    // Update last login
    $updateQuery = "UPDATE tbl_users SET login_session = ?, updated_at = NOW() WHERE id = ?";
    $db->query($updateQuery, [session_id(), $user['id']]);

    // Handle "Remember Me"
    if ($remember) {
        // Set cookie for 30 days
        $token = bin2hex(random_bytes(32));
        setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
    }

    jsonResponse(true, 'Login successful', [
        'user_id' => $user['id'],
        'username' => $user['username'],
        'email' => $user['email']
    ], 200);

} catch (\Exception $e) {
    error_log("Login error: " . $e->getMessage());
    jsonResponse(false, 'An error occurred during login', [], 500);
}
?>