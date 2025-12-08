<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include(__DIR__ . "/../../action/db/cn.php");

if (!$cn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$cn->set_charset("utf8");

$response = [
    'total_employees'  => getTotalEmployees($cn),
    'active_employees' => getActiveEmployees($cn),
    'pending_leaves'   => getPendingLeaves($cn),
    'on_leave_today'   => getOnLeaveToday($cn)
];

echo json_encode(['success' => true, 'data' => $response]);
$cn->close();


function getTotalEmployees($cn) {
    $sql = "SELECT COUNT(*) AS count FROM tbl_employees WHERE deleted_at";
    return (int)$cn->query($sql)->fetch_assoc()['count'];
}

function getActiveEmployees($cn) {
    $sql = "SELECT COUNT(*) AS count FROM tbl_employees WHERE status_id = 1 AND deleted_at IS NULL";
    return (int)$cn->query($sql)->fetch_assoc()['count'];
}

function getPendingLeaves($cn) {
    $sql = "SELECT COUNT(*) AS count FROM tbl_leave_applications WHERE status_id = 0 AND deleted_at IS NULL";
    return (int)$cn->query($sql)->fetch_assoc()['count'];
}

function getOnLeaveToday($cn) {
    $today = date('Y-m-d');
    $sql = "SELECT COUNT(DISTINCT employee_id) AS count
            FROM tbl_leave_applications
            WHERE '$today' BETWEEN start_date AND end_date
            AND status_id = 1 AND deleted_at IS NULL";
    return (int)$cn->query($sql)->fetch_assoc()['count'];
}
?>
