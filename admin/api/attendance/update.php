<?php
header('Content-Type: application/json');
session_start();

require_once(__DIR__ . "/../../action/db/cn.php");
require_once(__DIR__ . "/../../utils/response.php");

// Allow PUT and PATCH only
if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'PATCH') {
    jsonErrorResponse("Method not allowed. Use PUT or PATCH.", [], 405);
}

// Check DB connection
if (!isset($cn) || $cn->connect_error) {
    jsonErrorResponse("Database connection failed: " . ($cn->connect_error ?? ''), [], 500);
}
$cn->set_charset("utf8");

// Get ID from query
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
if (!$id) {
    jsonErrorResponse("Attendance record ID is required", [], 400);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    jsonErrorResponse("Invalid JSON input", [], 400);
}

// Check if record exists at all
$check_sql = "SELECT id, deleted_at FROM tbl_attendance_records WHERE id = ?";
$check_stmt = $cn->prepare($check_sql);
$check_stmt->bind_param("i", $id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    jsonErrorResponse("Attendance record not found", [], 404);
}

$record = $check_result->fetch_assoc();
if ($record['deleted_at'] !== null) {
    jsonErrorResponse("Attendance record is deleted and cannot be updated", [], 400);
}

// Build update query
$allowed_fields = ['employee_id', 'date', 'check_time', 'check_type_id', 'status_id'];
$update_fields = [];
$params = [];
$types = "";

// Only include fields present in input
foreach ($allowed_fields as $field) {
    if (isset($input[$field])) {
        $update_fields[] = "$field = ?";
        $params[] = $input[$field];
        $types .= in_array($field, ['employee_id', 'check_type_id', 'status_id']) ? "i" : "s";
    }
}

if (empty($update_fields)) {
    jsonErrorResponse("No fields to update", [], 400);
}

// Add updated_at and updated_by
$update_fields[] = "updated_at = NOW()";
$update_fields[] = "updated_by = ?";
$params[] = $_SESSION['user_id'] ?? null;
$types .= "i";

// Add ID for WHERE clause
$params[] = $id;
$types .= "i";

// Execute update
$sql = "UPDATE tbl_attendance_records SET " . implode(", ", $update_fields) . " WHERE id = ?";
$stmt = $cn->prepare($sql);
if (!$stmt) jsonErrorResponse("Failed to prepare update: " . $cn->error, [], 500);

$stmt->bind_param($types, ...$params);
if (!$stmt->execute()) {
    jsonErrorResponse("Failed to update record: " . $stmt->error, [], 500);
}

// Fetch updated record
$fetch_sql = "SELECT ar.*, ct.name as check_type_name 
              FROM tbl_attendance_records ar
              LEFT JOIN tbl_check_types ct ON ar.check_type_id = ct.id
              WHERE ar.id = ?";
$fetch_stmt = $cn->prepare($fetch_sql);
$fetch_stmt->bind_param("i", $id);
$fetch_stmt->execute();
$record = $fetch_stmt->get_result()->fetch_assoc();
$fetch_stmt->close();

// Return JSON
jsonResponse("Attendance record updated successfully", $record);

// Close connections
$stmt->close();
$check_stmt->close();
$cn->close();
