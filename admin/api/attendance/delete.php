<?php
header('Content-Type: application/json');
session_start();

include(__DIR__ . "/../../action/db/cn.php");
include(__DIR__ . "/../../utils/response.php");
include(__DIR__ . "/../../utils/sql_helper.php");

// Only allow DELETE method
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    jsonErrorResponse("Method not allowed. Use DELETE.", [], 405);
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

// Check if record exists and is not already deleted
$check_sql = "SELECT id FROM tbl_attendance_records WHERE id = ? AND deleted_at IS NULL";
$check_stmt = $cn->prepare($check_sql);
$check_stmt->bind_param("i", $id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    jsonErrorResponse("Attendance record not found or already deleted", [], 404);
}

// Soft delete
$sql = "UPDATE tbl_attendance_records 
        SET deleted_at = NOW(), 
            deleted_by = ? 
        WHERE id = ?";

$stmt = $cn->prepare($sql);
$deleted_by = $_SESSION['user_id'] ?? null;

$stmt->bind_param("ii", $deleted_by, $id);

if ($stmt->execute()) {
    jsonResponse("Attendance record deleted successfully");
} else {
    jsonErrorResponse("Failed to delete record: " . $stmt->error, [], 500);
}

$stmt->close();
$check_stmt->close();
$cn->close();
?>