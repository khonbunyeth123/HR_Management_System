<?php
session_start();
include(__DIR__ . "/../action/db/cn.php");

$cn->set_charset("utf8");
if ($cn->connect_error) {
    die("Connection failed: " . $cn->connect_error);
}

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$pass = isset($_POST['password']) ? trim($_POST['password']) : '';

$res = ['dpl' => false, 'message' => ''];

$_SESSION['login'] = false;
$_SESSION['uid'] = 0;

// Prepare the query (using placeholders for safety)
$stmt = $cn->prepare("
    SELECT 
        id,
        uuid,
        username,
        password,
        full_name,
        email,
        role,
        status_id,
        login_session,
        created_at,
        created_by,
        updated_at,
        updated_by,
        deleted_at,
        deleted_by
    FROM tbl_users
    WHERE email = ? AND deleted_at IS NULL
    LIMIT 1
");

// Bind the email parameter
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();

    // Check if user is active
    if ($user['status_id'] != 1) {
        $res['message'] = 'User account is inactive';
        echo json_encode($res);
        exit;
    }

    // Verify password
    if (password_verify($pass, $user['password'])) {

        // Generate secure login_session token
        $loginSession = bin2hex(random_bytes(32));

        // Update login_session and updated_at in DB
        $updateStmt = $cn->prepare("UPDATE tbl_users SET login_session = ?, updated_at = NOW() WHERE uuid = ?");
        $updateStmt->bind_param("ss", $loginSession, $user['uuid']);
        $updateStmt->execute();

        // Set session variables
        $_SESSION['login'] = true;
        $_SESSION['uid'] = $user['id'];
        $_SESSION['login_session'] = $loginSession;
        $_SESSION['uuid'] = $user['uuid'];
        $_SESSION['uemail'] = $user['email'];
        $_SESSION['uname'] = $user['username'];
        $_SESSION['ufull_name'] = $user['full_name'];
        $_SESSION['utype'] = $user['role'];

        $res['dpl'] = true;
        $res['message'] = 'Login successful';
    } else {
        $res['message'] = 'Invalid credentials';
    }
} else {
    $res['message'] = 'User not found';
}

echo json_encode($res);
exit();