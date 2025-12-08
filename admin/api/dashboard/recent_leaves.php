<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include(__DIR__ . "/../../action/db/cn.php");

if (!$cn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$cn->set_charset("utf8");

$sql = "SELECT 
            e.full_name,
            la.leave_type,
            la.start_date,
            la.end_date,
            la.status_id
        FROM tbl_leave_applications la
        JOIN tbl_employees e ON la.employee_id = e.id
        WHERE la.deleted_at IS NULL
        ORDER BY la.created_at DESC
        LIMIT 4";

$result = $cn->query($sql);
$leaves = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $leaves[] = [
            'name' => $row['full_name'],
            'type' => ucfirst($row['leave_type']),
            'period' => $row['start_date'] . " to " . $row['end_date'],
            'status' => ($row['status_id'] == 0 ? "Pending" :
                        ($row['status_id'] == 1 ? "Approved" : "Rejected"))
        ];
    }
}

echo json_encode(['success' => true, 'data' => $leaves]);
$cn->close();
?>
