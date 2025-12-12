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

// Get parameters
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');
$employee_id = $_GET['employee_id'] ?? null;

// Validate dates
if (!strtotime($start_date) || !strtotime($end_date)) {
    jsonErrorResponse("Invalid date format. Use YYYY-MM-DD", [], 400);
}

// Build SQL for summary statistics
$summary_sql = "SELECT 
    COUNT(*) as total_records,
    SUM(CASE WHEN check_type_id IN (1,3) THEN 1 ELSE 0 END) as total_check_ins,
    SUM(CASE WHEN check_type_id IN (2,4) THEN 1 ELSE 0 END) as total_check_outs,
    COUNT(DISTINCT employee_id) as unique_employees,
    MIN(date) as earliest_date,
    MAX(date) as latest_date
    FROM tbl_attendance_records 
    WHERE date BETWEEN ? AND ? 
    AND deleted_at IS NULL";

$summary_params = [$start_date, $end_date];
$summary_types = "ss";

if ($employee_id) {
    $summary_sql .= " AND employee_id = ?";
    $summary_params[] = $employee_id;
    $summary_types .= "i";
}

// Execute summary query
$summary_stmt = $cn->prepare($summary_sql);
$summary_stmt->bind_param($summary_types, ...$summary_params);
$summary_stmt->execute();
$summary = $summary_stmt->get_result()->fetch_assoc();

// Get daily statistics
$daily_sql = "SELECT 
    date,
    COUNT(*) as daily_records,
    SUM(CASE WHEN check_type_id IN (1,3) THEN 1 ELSE 0 END) as daily_check_ins,
    SUM(CASE WHEN check_type_id IN (2,4) THEN 1 ELSE 0 END) as daily_check_outs
    FROM tbl_attendance_records 
    WHERE date BETWEEN ? AND ? 
    AND deleted_at IS NULL";

$daily_params = [$start_date, $end_date];
$daily_types = "ss";

if ($employee_id) {
    $daily_sql .= " AND employee_id = ?";
    $daily_params[] = $employee_id;
    $daily_types .= "i";
}

$daily_sql .= " GROUP BY date ORDER BY date DESC";

$daily_stmt = $cn->prepare($daily_sql);
$daily_stmt->bind_param($daily_types, ...$daily_params);
$daily_stmt->execute();
$daily_stats = $daily_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get employee statistics
$employee_sql = "SELECT 
    employee_id,
    e.name as employee_name,
    COUNT(*) as employee_records,
    SUM(CASE WHEN check_type_id IN (1,3) THEN 1 ELSE 0 END) as employee_check_ins,
    SUM(CASE WHEN check_type_id IN (2,4) THEN 1 ELSE 0 END) as employee_check_outs
    FROM tbl_attendance_records ar
    LEFT JOIN tbl_employees e ON ar.employee_id = e.id
    WHERE date BETWEEN ? AND ? 
    AND ar.deleted_at IS NULL";

$employee_params = [$start_date, $end_date];
$employee_types = "ss";

$employee_sql .= " GROUP BY employee_id ORDER BY employee_records DESC";

$employee_stmt = $cn->prepare($employee_sql);
$employee_stmt->bind_param($employee_types, ...$employee_params);
$employee_stmt->execute();
$employee_stats = $employee_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Compile report data
$report_data = [
    "summary" => $summary,
    "date_range" => [
        "start_date" => $start_date,
        "end_date" => $end_date
    ],
    "daily_statistics" => $daily_stats,
    "employee_statistics" => $employee_stats,
    "report_generated_at" => date('Y-m-d H:i:s')
];

if ($employee_id) {
    $report_data["employee_id"] = $employee_id;
}

jsonResponse("Attendance report generated successfully", $report_data);

$summary_stmt->close();
$daily_stmt->close();
$employee_stmt->close();
$cn->close();
?>