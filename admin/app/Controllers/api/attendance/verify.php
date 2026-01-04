<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include(__DIR__ . "/../../action/db/cn.php");

$input = json_decode(file_get_contents('php://input'), true);
$scan_data = $input['qr_data'] ?? $input['employee_code'] ?? null;

if (!$scan_data) {
    echo json_encode(['success' => false, 'message' => 'Scan data required']);
    exit;
}

// Extract employee info
$employee = null;
if (is_numeric($scan_data)) {
    $sql = "SELECT id, name, employee_code FROM tbl_employees WHERE id = ? AND status_id = 1";
    $stmt = $cn->prepare($sql);
    $stmt->bind_param("i", (int)$scan_data);
    $stmt->execute();
    $employee = $stmt->get_result()->fetch_assoc();
} elseif (strpos($scan_data, 'EMP:') === 0) {
    $parts = explode(':', $scan_data);
    $employee_id = (int)($parts[1] ?? 0);
    $sql = "SELECT id, name, employee_code FROM tbl_employees WHERE id = ? AND status_id = 1";
    $stmt = $cn->prepare($sql);
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $employee = $stmt->get_result()->fetch_assoc();
} else {
    // Try employee code
    $sql = "SELECT id, name, employee_code FROM tbl_employees WHERE employee_code = ? AND status_id = 1";
    $stmt = $cn->prepare($sql);
    $stmt->bind_param("s", $scan_data);
    $stmt->execute();
    $employee = $stmt->get_result()->fetch_assoc();
}

if ($employee) {
    // Get today's last check
    $sql = "SELECT check_type_id, check_time FROM tbl_attendance_records 
            WHERE employee_id = ? AND date = CURDATE() AND deleted_at IS NULL 
            ORDER BY check_time DESC LIMIT 1";
    $stmt = $cn->prepare($sql);
    $stmt->bind_param("i", $employee['id']);
    $stmt->execute();
    $last_check = $stmt->get_result()->fetch_assoc();
    
    // Determine next action
    $next_action = 'Check-in (Morning)';
    if ($last_check) {
        $next_type = ($last_check['check_type_id'] % 4) + 1;
        $actions = [1 => 'Check-in', 2 => 'Lunch Break', 3 => 'Check-in', 4 => 'Check-out'];
        $next_action = $actions[$next_type] ?? 'Check-in';
    }
    
    echo json_encode([
        'success' => true,
        'verified' => true,
        'employee' => $employee,
        'last_check' => $last_check,
        'next_action' => $next_action,
        'message' => 'Employee verified. Ready for ' . strtolower($next_action) . '.'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'verified' => false,
        'message' => 'Employee not found or inactive'
    ]);
}
?>