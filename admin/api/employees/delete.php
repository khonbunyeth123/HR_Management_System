<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method not allowed. Use POST."
    ]);
    exit;
}

session_start();

include(__DIR__ . "/../../action/db/cn.php");
include(__DIR__ . "/../../utils/response.php");

// Set timezone for Phnom Penh
date_default_timezone_set("Asia/Phnom_Penh");

// Ensure MySQL also uses Phnom Penh timezone
$cn->query("SET time_zone = '+07:00'");

// Check DB connection
if (!isset($cn) || $cn->connect_error) {
    jsonErrorResponse("Database connection failed", [], 500);
    exit;
}

// Get and validate input
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    jsonErrorResponse("Invalid JSON input: " . json_last_error_msg());
    exit;
}

if (empty($input['id'])) {
    jsonErrorResponse("Employee ID is required");
    exit;
}

$employeeId = (int)$input['id'];

try {
    // Check employee exists
    $checkStmt = $cn->prepare("
        SELECT id, CONCAT(first_name, ' ', last_name) AS full_name
        FROM tbl_employees
        WHERE id = ? AND deleted_at IS NULL
    ");
    $checkStmt->bind_param("i", $employeeId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    $employee = $result->fetch_assoc();
    $checkStmt->close();

    if (!$employee) {
        jsonErrorResponse("Employee not found or already deleted");
        exit;
    }

    // Perform soft delete
    $deleteStmt = $cn->prepare("
        UPDATE tbl_employees
        SET deleted_at = ?
        WHERE id = ?
    ");
    
    $deletedAt = date("Y-m-d H:i:s"); // Phnom Penh time
    $deleteStmt->bind_param("si", $deletedAt, $employeeId);

    if ($deleteStmt->execute()) {
        $deleteStmt->close();
        echo json_encode([
            "success" => true,
            "message" => "Employee '{$employee['full_name']}' has been deleted successfully",
            "deleted_at" => $deletedAt,
            "deleted_id" => $employeeId
        ]);
    } else {
        throw new Exception("Failed to delete employee: " . $deleteStmt->error);
    }

} catch (Exception $e) {
    error_log("Delete employee error: " . $e->getMessage());
    jsonErrorResponse("Failed to delete employee: " . $e->getMessage());
}

$cn->close();
?>