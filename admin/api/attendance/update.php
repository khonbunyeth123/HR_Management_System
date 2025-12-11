<?php
header('Content-Type: application/json');
session_start();

include(__DIR__ . "/../../action/db/cn.php");
include(__DIR__ . "/../../utils/response.php");
include(__DIR__ . "/../../utils/sql_helper.php");

// Allow both PUT and PATCH methods
if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'PATCH') {
    jsonErrorResponse("Method not allowed. Use PUT or PATCH.", [], 405);
}

if (!isset($cn)) {
    jsonErrorResponse("Database connection not initialized", [], 500);
}

$cn->set_charset("utf8");
if ($cn->connect_error) {
    jsonErrorResponse("Connection failed: " . $cn->connect_error, [], 500);
}

// Get ID from query parameter
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
if (!$id) {
    jsonErrorResponse("Attendance record ID is required", [], 400);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    jsonErrorResponse("Invalid JSON input", [], 400);
}

// Check if record exists
$check_sql = "SELECT id FROM tbl_attendance_records WHERE id = ? AND deleted_at IS NULL";
$check_stmt = $cn->prepare($check_sql);
$check_stmt->bind_param("i", $id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    jsonErrorResponse("Attendance record not found", [], 404);
}

// Build update query dynamically based on provided fields
$allowed_fields = ['employee_id', 'date', 'check_time', 'check_type_id', 'status_id'];
$update_fields = [];
$params = [];
$types = "";

foreach ($allowed_fields as $field) {
    if (isset($input[$field])) {
        $update_fields[] = "$field = ?";
        $params[] = $input[$field];
        
        // Determine parameter type
        if (in_array($field, ['employee_id', 'check_type_id', 'status_id'])) {
            $types .= "i"; // integer
        } else {
            $types .= "s"; // string
        }
    }
}

if (empty($update_fields)) {
    jsonErrorResponse("No fields to update", [], 400);
}

// Add updated_by and updated_at
$update_fields[] = "updated_at = NOW()";
$update_fields[] = "updated_by = ?";
$params[] = $_SESSION['user_id'] ?? null;
$types .= "i";

// Add ID as last parameter
$params[] = $id;
$types .= "i";

// Build and execute update query
$sql = "UPDATE tbl_attendance_records SET " . implode(", ", $update_fields) . " WHERE id = ?";
$stmt = $cn->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    // Fetch updated record
    $fetch_sql = "SELECT * FROM tbl_attendance_records WHERE id = ?";
    $fetch_stmt = $cn->prepare($fetch_sql);
    $fetch_stmt->bind_param("i", $id);
    $fetch_stmt->execute();
    $record = $fetch_stmt->get_result()->fetch_assoc();
    
    jsonResponse("Attendance record updated successfully", $record);
} else {
    jsonErrorResponse("Failed to update record: " . $stmt->error, [], 500);
}

$stmt->close();
$check_stmt->close();
$cn->close();
?>