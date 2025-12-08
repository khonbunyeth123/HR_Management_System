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
            department AS name,
            COUNT(id) AS count,
            ROUND((COUNT(id) * 100.0 / (SELECT COUNT(*) FROM tbl_employees WHERE deleted_at IS NULL)), 1) AS percentage
        FROM tbl_employees
        WHERE deleted_at IS NULL
        GROUP BY department
        ORDER BY count DESC";

$result = $cn->query($sql);
$departments = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $departments[] = [
            'name' => $row['name'],
            'count' => (int)$row['count'],
            'percentage' => (float)$row['percentage']
        ];
    }
}

echo json_encode(['success' => true, 'data' => $departments]);
$cn->close();
?>
