<?php
header('Content-Type: application/json');

include(__DIR__ . "/../../action/db/cn.php");

$input = json_decode(file_get_contents("php://input"), true);

$id            = $input['id'] ?? null;
$start_date    = $input['start_date'] ?? null;
$end_date      = $input['end_date'] ?? null;
$reason        = $input['reason'] ?? null;

if (!$id) {
    echo json_encode(["success" => false, "message" => "Leave ID required"]);
    exit;
}

$sql = "
UPDATE tbl_leave_applications 
SET start_date = ?, end_date = ?, reason = ?
WHERE id = ? AND status_id = 0
";

$stmt = $cn->prepare($sql);
$stmt->bind_param("sssi", $start_date, $end_date, $reason, $id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Leave updated"]);
} else {
    echo json_encode(["success" => false, "message" => "Update failed"]);
}
