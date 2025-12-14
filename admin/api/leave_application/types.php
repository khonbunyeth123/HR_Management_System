<?php
header('Content-Type: application/json');

include(__DIR__ . "/../../action/db/cn.php");

$sql = "SELECT id, name FROM tbl_leave_types WHERE status = 1";
$result = $cn->query($sql);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode([
    "success" => true,
    "data" => $data
]);
