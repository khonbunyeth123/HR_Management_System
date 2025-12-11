<?php
header('Content-Type: application/json');
session_start();

include(__DIR__ . "/../../action/db/cn.php");
include(__DIR__ . "/../../utils/response.php");
include(__DIR__ . "/../../utils/sql_helper.php");

// Only allow POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonErrorResponse("Method not allowed. Use POST.", [], 405);
}

if (!isset($cn)) {
    jsonErrorResponse("Database connection not initialized", [], 500);
}

$cn->set_charset("utf8");
if ($cn->connect_error) {
    jsonErrorResponse("Connection failed: " . $cn->connect_error, [], 500);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    jsonErrorResponse("Invalid JSON input", [], 400);
}

// Validate required fields
$required_fields = ['employee_id', 'date', 'check_time', 'check_type_id'];
foreach ($required_fields as $field) {
    if (!isset($input[$field]) || empty($input[$field])) {
        jsonErrorResponse("Missing required field: $field", [], 400);
    }
}

// Generate UUID
$uuid = bin2hex(random_bytes(16));

// Prepare SQL
$sql = "INSERT INTO tbl_attendance_records 
        (uuid, employee_id, date, check_time, check_type_id, status_id, created_at, created_by) 
        VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)";

$stmt = $cn->prepare($sql);
if (!$stmt) {
    jsonErrorResponse("Prepare failed: " . $cn->error, [], 500);
}

// Default status_id to 1 (Active)
$status_id = $input['status_id'] ?? 1;
$created_by = $_SESSION['user_id'] ?? null; // Assuming you have user session

$stmt->bind_param(
    "sisssii",
    $uuid,
    $input['employee_id'],
    $input['date'],
    $input['check_time'],
    $input['check_type_id'],
    $status_id,
    $created_by
);

if ($stmt->execute()) {
    $record_id = $stmt->insert_id;
    
    // Fetch the created record
    $fetch_sql = "SELECT * FROM tbl_attendance_records WHERE id = ?";
    $fetch_stmt = $cn->prepare($fetch_sql);
    $fetch_stmt->bind_param("i", $record_id);
    $fetch_stmt->execute();
    $record = $fetch_stmt->get_result()->fetch_assoc();
    
    jsonResponse("Attendance record created successfully", $record, 201);
} else {
    jsonErrorResponse("Failed to create record: " . $stmt->error, [], 500);
}

$stmt->close();
$cn->close();
?>