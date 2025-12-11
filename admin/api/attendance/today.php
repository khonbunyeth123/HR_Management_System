<?php
header('Content-Type: application/json');
session_start();

include(__DIR__ . "/../../action/db/cn.php");
include(__DIR__ . "/../../utils/response.php");
include(__DIR__ . "/../../utils/sql_helper.php");

// Only allow GET method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonErrorResponse("Method not allowed. Use GET.", [], 405);
}

if (!isset($cn)) {
    jsonErrorResponse("Database connection not initialized", [], 500);
}

$cn->set_charset("utf8");
if ($cn->connect_error) {
    jsonErrorResponse("Connection failed: " . $cn->connect_error, [], 500);
}

$today = date('Y-m-d');

// Build SQL
$sql = "SELECT 
    ar.*,
    ct.name as check_type_name,
    ct.standard_time
    FROM tbl_attendance_records ar
    LEFT JOIN tbl_check_types ct ON ar.check_type_id = ct.id
    WHERE ar.date = ? 
    AND ar.deleted_at IS NULL
    ORDER BY ar.check_time DESC";

$stmt = $cn->prepare($sql);
$stmt->bind_param("s", $today);
$stmt->execute();
$records = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get summary
$summary_sql = "SELECT 
    COUNT(*) as total_today,
    SUM(CASE WHEN check_type_id = 1 THEN 1 ELSE 0 END) as check_ins_today,
    SUM(CASE WHEN check_type_id = 2 THEN 1 ELSE 0 END) as check_outs_today
    FROM tbl_attendance_records 
    WHERE date = ? 
    AND deleted_at IS NULL";

$summary_stmt = $cn->prepare($summary_sql);
$summary_stmt->bind_param("s", $today);
$summary_stmt->execute();
$summary = $summary_stmt->get_result()->fetch_assoc();

$data = [
    "today_date" => $today,
    "summary" => $summary,
    "records" => $records
];

jsonResponse("Today's attendance records fetched successfully", $data);

$stmt->close();
$summary_stmt->close();
$cn->close();
?>