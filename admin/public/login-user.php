<?php
session_start();
header('Content-Type: application/json');

// Get your existing database connection
require_once __DIR__ . '/../database/cn.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'dpl' => false,
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

try {
    // Get email and password
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $remember = isset($_POST['remember']) && $_POST['remember'] == 1;

    // Validate input
    if (empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode([
            'dpl' => false,
            'success' => false,
            'message' => 'Email and password are required'
        ]);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            'dpl' => false,
            'success' => false,
            'message' => 'Invalid email format'
        ]);
        exit;
    }

    // Query the database for the user
    $query = "SELECT 
                u.id,
                u.uuid,
                u.username,
                u.password,
                u.full_name,
                u.email,
                u.role_id,
                r.name AS role_name,
                u.status_id
              FROM tbl_users u
              LEFT JOIN tbl_roles r ON u.role_id = r.id
              WHERE u.email = :email AND u.deleted_at IS NULL
              LIMIT 1";
    $stmt = $cn->prepare($query);
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(401);
        echo json_encode([
            'dpl' => false,
            'success' => false,
            'message' => 'Invalid email or password'
        ]);
        exit;
    }

    // Check if account is active
    if ($user['status_id'] != 1) {
        http_response_code(403);
        echo json_encode([
            'dpl' => false,
            'success' => false,
            'message' => 'Account is inactive. Please contact administrator.'
        ]);
        exit;
    }

    // Block Employee from logging in
    $roleName = strtolower($user['role_name'] ?? '');
    if ($roleName === 'employee') {
        http_response_code(403);
        echo json_encode([
            'dpl' => false,
            'success' => false,
            'message' => 'Employee accounts are not allowed to log in.'
        ]);
        exit;
    }

    // Verify password using bcrypt
    if (!password_verify($password, $user['password'])) {
        http_response_code(401);
        echo json_encode([
            'dpl' => false,
            'success' => false,
            'message' => 'Invalid email or password'
        ]);
        exit;
    }

    // Generate session token
    $session_token = bin2hex(random_bytes(32));

    // Update login session in database
    $update_query = "UPDATE tbl_users SET login_session = :session_token, updated_at = NOW() WHERE id = :id";
    $update_stmt = $cn->prepare($update_query);
    $update_stmt->execute([
        ':session_token' => $session_token,
        ':id' => $user['id']
    ]);

    // Set session variables
    $_SESSION['login'] = true;
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['uuid'] = $user['uuid'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role_name'] ?? '';
    $_SESSION['role_id'] = $user['role_id'] ?? null;
    $_SESSION['session_token'] = $session_token;

    // Set remember me cookie if requested
    if ($remember) {
        setcookie('remember_token', $session_token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
        $_SESSION['remember'] = true;
    }

    // Return success response
    http_response_code(200);
    echo json_encode([
        'dpl' => true,
        'success' => true,
        'message' => 'Login successful',
        'user' => [
            'id' => $user['id'],
            'uuid' => $user['uuid'],
            'username' => $user['username'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'role' => $user['role_name'] ?? '',
            'role_id' => $user['role_id'] ?? null
        ]
    ]);
    exit;

} catch (PDOException $e) {
    error_log('Login error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'dpl' => false,
        'success' => false,
        'message' => 'Database error occurred'
    ]);
    exit;
} catch (Exception $e) {
    error_log('Login error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'dpl' => false,
        'success' => false,
        'message' => 'An error occurred. Please try again.'
    ]);
    exit;
}
?>
