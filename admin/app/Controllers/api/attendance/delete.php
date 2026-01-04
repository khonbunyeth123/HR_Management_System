<?php
header('Content-Type: application/json');
session_start();

require_once(__DIR__ . "/../../action/db/cn.php");
require_once(__DIR__ . "/../../utils/response.php");

// Allow DELETE only
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    jsonErrorResponse("Only DELETE method allowed.", [], 405);
}

// Check database connection
if (!$cn || $cn->connect_errno) {
    jsonErrorResponse("Database not connected.", [], 500);
}

$cn->set_charset("utf8");

// Validate ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    jsonErrorResponse("Invalid or missing attendance ID.", [], 400);
}

// Check if record exists and not deleted
$sql = "SELECT id FROM tbl_attendance_records WHERE id = ? AND deleted_at IS NULL LIMIT 1";
$stmt = $cn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    jsonErrorResponse("Record not found or already deleted.", [], 404);
}

// Soft delete
$deleted_by = $_SESSION['user_id'] ?? null;

$delete_sql = "UPDATE tbl_attendance_records SET deleted_at = NOW(), deleted_by = ? WHERE id = ? LIMIT 1";
$delete_stmt = $cn->prepare($delete_sql);
$delete_stmt->bind_param("ii", $deleted_by, $id);

if ($delete_stmt->execute()) {
    jsonResponse("Record deleted successfully.");
} else {
    jsonErrorResponse("Delete failed: " . $delete_stmt->error, [], 500);
}

$delete_stmt->close();
$stmt->close();
$cn->close();
