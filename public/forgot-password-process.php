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

    if (empty($email)) {
        jsonResponse(false, 'Email is required', 400);
    }

    $db = \App\Core\Database::getInstance();

    // Check if user or employee exists
    $userQuery = "SELECT id FROM tbl_users WHERE email = ? AND status_id = 1 LIMIT 1";
    $user = $db->query($userQuery, [$email]);

    $employeeQuery = "SELECT id FROM tbl_employees WHERE email = ? AND status_id = 1 LIMIT 1";
    $employee = $db->query($employeeQuery, [$email]);

    if (!empty($user) || !empty($employee)) {
        // Generate token
        $token = bin2hex(random_bytes(32));
        
        // Save to password_resets table
        $db->query("DELETE FROM password_resets WHERE email = ?", [$email]);
        $db->query("INSERT INTO password_resets (email, token, created_at) VALUES (?, ?, NOW())", [$email, $token]);

        // LOG THE TOKEN FOR TESTING (Since no mailer is integrated)
        error_log("Password reset requested for $email. Token: $token");
        // In a real app, send email here:
        // $resetLink = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]/reset-password.php?token=$token&email=" . urlencode($email);
        // sendEmail($email, "Password Reset", "Click here to reset your password: $resetLink");
    }

    // Always return success for security
    jsonResponse(true, 'If your email is in our system, you will receive instructions shortly.');

} catch (\Exception $e) {
    error_log("Forgot password error: " . $e->getMessage());
    jsonResponse(false, 'An error occurred. Please try again later.', 500);
}
?>
