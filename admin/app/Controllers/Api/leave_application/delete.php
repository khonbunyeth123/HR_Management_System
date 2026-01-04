<?php
header('Content-Type: application/json');

include(__DIR__ . "/../../action/db/cn.php");

$input = json_decode(file_get_contents("php://input"), true);
$id = $input['id'] ?? null;

if (!$id) {
    echo json_encode(["success" => false, "message" => "Leave ID required"]);
    exit;
}

$sql = "DELETE FROM tbl_leave_applications WHERE id = ? AND status_id = 0";
$stmt = $cn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Leave deleted"]);
} else {
    echo json_encode(["success" => false, "message" => "Delete failed"]);
}
