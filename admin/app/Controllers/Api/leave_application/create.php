<?php
// =======================
// Leave Application API
// =======================

// Enable full error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// -----------------------
// Include helpers & DB
// -----------------------
$cn_path = __DIR__ . "/../../action/db/cn.php";
$response_path = __DIR__ . "/../../utils/response.php";

if (!file_exists($cn_path) || !file_exists($response_path)) {
    echo json_encode([
        "success" => false,
        "message" => "Required include files not found",
        "paths" => [
            "cn.php" => $cn_path,
            "response.php" => $response_path
        ]
    ], JSON_PRETTY_PRINT);
    exit;
}

include($cn_path);
include($response_path);

// Check DB connection
if (!isset($cn) || $cn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed",
        "error" => $cn->connect_error ?? "Unknown"
    ], JSON_PRETTY_PRINT);
    exit;
}

$cn->set_charset("utf8");

// -----------------------
// Only POST allowed
// -----------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "success" => false,
        "message" => "Method not allowed",
        "method" => $_SERVER['REQUEST_METHOD']
    ], JSON_PRETTY_PRINT);
    exit;
}

// -----------------------
// Read JSON input
// -----------------------
$input = json_decode(file_get_contents("php://input"), true);

if (!$input) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid JSON input",
        "raw_input" => file_get_contents("php://input")
    ], JSON_PRETTY_PRINT);
    exit;
}

// -----------------------
// Required fields
// -----------------------
$required = ['employee_id', 'leave_type_id', 'start_date', 'end_date', 'reason'];
foreach ($required as $field) {
    if (empty($input[$field])) {
        echo json_encode([
            "success" => false,
            "message" => "Missing required field: $field"
        ], JSON_PRETTY_PRINT);
        exit;
    }
}

// -----------------------
// Assign variables
// -----------------------
$uuid = bin2hex(random_bytes(16));
$employee_id = (int) $input['employee_id'];
$leave_type_id = (int) $input['leave_type_id'];
$start_date = $input['start_date'];
$end_date = $input['end_date'];
$reason = $input['reason'];

// Validate dates
if ($end_date < $start_date) {
    echo json_encode([
        "success" => false,
        "message" => "End date cannot be before start date"
    ], JSON_PRETTY_PRINT);
    exit;
}

// -----------------------
// Validate leave_type_id exists
// -----------------------
$result = $cn->query("SELECT id FROM tbl_leave_types WHERE id = $leave_type_id");
if ($result->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid leave_type_id"
    ], JSON_PRETTY_PRINT);
    exit;
}

// -----------------------
// Insert into database (using leave_type_id instead of name)
// -----------------------
$sql = "INSERT INTO tbl_leave_applications
        (uuid, employee_id, leave_type_id, start_date, end_date, reason, created_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())";

$stmt = $cn->prepare($sql);

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Failed to prepare SQL statement",
        "error" => $cn->error
    ], JSON_PRETTY_PRINT);
    exit;
}

$stmt->bind_param(
    "siisss",
    $uuid,
    $employee_id,
    $leave_type_id,
    $start_date,
    $end_date,
    $reason
);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Leave application created successfully",
        "data" => [
            "id" => $stmt->insert_id,
            "uuid" => $uuid
        ]
    ], JSON_PRETTY_PRINT);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Failed to execute SQL statement",
        "error" => $stmt->error
    ], JSON_PRETTY_PRINT);
}

$cn->close();