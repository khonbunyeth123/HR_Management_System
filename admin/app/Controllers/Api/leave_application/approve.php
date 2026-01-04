<?php
header('Content-Type: application/json');
include(__DIR__ . "/../../action/db/cn.php");

$input = json_decode(file_get_contents("php://input"), true);

$id = $input['id'] ?? null;          // uuid
$statusId = $input['status_id'] ?? null;

if (!$id || $statusId === null) {
    echo json_encode(["success" => false, "message" => "Invalid data"]);
    exit;
}

$sql = "
    UPDATE tbl_leave_applications 
    SET status_id = ?
    WHERE uuid = ? AND status_id = 0
";

$stmt = $cn->prepare($sql);
$stmt->bind_param("is", $statusId, $id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(["success" => true, "message" => "Leave status updated"]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Action failed or leave already processed"
    ]);
}
