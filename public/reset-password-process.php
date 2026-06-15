<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Load .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

use App\Services\AuthService;

function jsonResponse($success, $message, $code = 200) {
    header('Content-Type: application/json');
    http_response_code($code);
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(false, 'Method not allowed', 405);
    }

    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $token = isset($_POST['token']) ? trim($_POST['token']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    $authService = new AuthService();
    $result = $authService->resetPassword($email, $token, $password);

    jsonResponse($result['success'], $result['message'], $result['code']);

} catch (\Exception $e) {
    error_log("Reset password error: " . $e->getMessage());
    jsonResponse(false, 'An error occurred. Please try again later.', 500);
}
?>
