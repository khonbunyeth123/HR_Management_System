<?php
header('Content-Type: application/json');

include(__DIR__ . "/../../action/db/cn.php");

$where = [];

if (!empty($_GET['employee_id'])) {
    $where[] = "la.employee_id = " . intval($_GET['employee_id']);
}

if (isset($_GET['status_id'])) {
    $where[] = "la.status_id = " . intval($_GET['status_id']);
}

$whereSql = count($where) ? "WHERE " . implode(" AND ", $where) : "";

$sql = "
SELECT 
    la.id,
    la.employee_id,
    e.name AS employee_name,
    lt.name AS leave_type,
    la.start_date,
    la.end_date,
    la.reason,
    la.status_id,
    la.created_at
FROM tbl_leave_applications la
JOIN tbl_employees e ON la.employee_id = e.id
JOIN tbl_leave_types lt ON la.leave_type_id = lt.id
$whereSql
ORDER BY la.created_at DESC
";

$result = $cn->query($sql);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode([
    "success" => true,
    "data" => $data
]);
