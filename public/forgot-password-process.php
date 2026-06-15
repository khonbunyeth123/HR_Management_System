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

    $authService = new AuthService();
    $result = $authService->requestPasswordReset($email);

    jsonResponse($result['success'], $result['message'], $result['code']);

} catch (\Exception $e) {
    error_log("Forgot password error: " . $e->getMessage());
    jsonResponse(false, 'An error occurred. Please try again later.', 500);
}
?>
