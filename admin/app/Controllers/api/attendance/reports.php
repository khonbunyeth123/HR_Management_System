<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
session_start();

// ------------------------
// Includes
// ------------------------
require_once(__DIR__ . "/../../action/db/cn.php");      // Database connection
require_once(__DIR__ . "/../../utils/response.php");    // JSON response helper

// ------------------------
// Only allow GET
// ------------------------
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonErrorResponse("Method not allowed. Use GET.", [], 405);
}

// ------------------------
// Check DB connection
// ------------------------
if (!isset($cn) || $cn->connect_errno) {
    jsonErrorResponse("Database connection failed: " . ($cn->connect_error ?? ''), [], 500);
}

$cn->set_charset("utf8");

// ------------------------
// Get parameters
// ------------------------
$start_date  = $_GET['start_date'] ?? date('Y-m-01'); // First day of this month
$end_date    = $_GET['end_date'] ?? date('Y-m-t');    // Last day of this month
$employee_id = $_GET['employee_id'] ?? null;

// Validate dates
if (!strtotime($start_date) || !strtotime($end_date)) {
    jsonErrorResponse("Invalid date format. Use YYYY-MM-DD", [], 400);
}

// ------------------------
// Summary statistics
// ------------------------
$summary_sql = "SELECT 
    COUNT(*) as total_records,
    SUM(CASE WHEN check_type_id IN (1,3) THEN 1 ELSE 0 END) as total_check_ins,
    SUM(CASE WHEN check_type_id IN (2,4) THEN 1 ELSE 0 END) as total_check_outs,
    COUNT(DISTINCT employee_id) as unique_employees,
    MIN(date) as earliest_date,
    MAX(date) as latest_date
FROM tbl_attendance_records
WHERE date BETWEEN ? AND ? AND deleted_at IS NULL";

$types = "ss";
$params = [$start_date, $end_date];

if ($employee_id) {
    $summary_sql .= " AND employee_id = ?";
    $types .= "i";
    $params[] = $employee_id;
}

$stmt = $cn->prepare($summary_sql);
if (!$stmt) jsonErrorResponse("Failed to prepare summary query: " . $cn->error, [], 500);

$stmt->bind_param($types, ...$params);
$stmt->execute();
$summary = $stmt->get_result()->fetch_assoc();
$stmt->close();

// ------------------------
// Daily statistics
// ------------------------
$daily_sql = "SELECT 
    date,
    COUNT(*) as daily_records,
    SUM(CASE WHEN check_type_id IN (1,3) THEN 1 ELSE 0 END) as daily_check_ins,
    SUM(CASE WHEN check_type_id IN (2,4) THEN 1 ELSE 0 END) as daily_check_outs
FROM tbl_attendance_records
WHERE date BETWEEN ? AND ? AND deleted_at IS NULL";

$daily_types = "ss";
$daily_params = [$start_date, $end_date];

if ($employee_id) {
    $daily_sql .= " AND employee_id = ?";
    $daily_types .= "i";
    $daily_params[] = $employee_id;
}

$daily_sql .= " GROUP BY date ORDER BY date DESC";

$daily_stmt = $cn->prepare($daily_sql);
if (!$daily_stmt) jsonErrorResponse("Failed to prepare daily query: " . $cn->error, [], 500);

$daily_stmt->bind_param($daily_types, ...$daily_params);
$daily_stmt->execute();
$daily_stats = $daily_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$daily_stmt->close();

// ------------------------
// Employee statistics
// ------------------------
$employee_sql = "SELECT 
    e.id as employee_id,
    e.full_name as employee_name,
    COUNT(*) as employee_records,
    SUM(CASE WHEN check_type_id IN (1,3) THEN 1 ELSE 0 END) as employee_check_ins,
    SUM(CASE WHEN check_type_id IN (2,4) THEN 1 ELSE 0 END) as employee_check_outs
FROM tbl_attendance_records ar
LEFT JOIN tbl_employees e ON ar.employee_id = e.id
WHERE date BETWEEN ? AND ? AND ar.deleted_at IS NULL
GROUP BY e.id, e.full_name
ORDER BY employee_records DESC";

$employee_types = "ss";
$employee_params = [$start_date, $end_date];

$employee_stmt = $cn->prepare($employee_sql);
if (!$employee_stmt) jsonErrorResponse("Failed to prepare employee query: " . $cn->error, [], 500);

$employee_stmt->bind_param($employee_types, ...$employee_params);
$employee_stmt->execute();
$employee_stats = $employee_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$employee_stmt->close();

// ------------------------
// Compile report
// ------------------------
$report_data = [
    "summary" => $summary,
    "date_range" => ["start_date" => $start_date, "end_date" => $end_date],
    "daily_statistics" => $daily_stats,
    "employee_statistics" => $employee_stats,
    "report_generated_at" => date('Y-m-d H:i:s')
];

if ($employee_id) {
    $report_data["employee_id"] = $employee_id;
}

// Return JSON
jsonResponse("Attendance report generated successfully", $report_data);

// Close DB
$cn->close();
