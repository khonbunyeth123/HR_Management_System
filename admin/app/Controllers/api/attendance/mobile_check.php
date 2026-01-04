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

// Extract employee ID
$employee_id = null;
if (is_numeric($scan_data)) {
    $employee_id = (int)$scan_data;
} elseif (strpos($scan_data, 'EMP:') === 0) {
    $parts = explode(':', $scan_data);
    $employee_id = (int)($parts[1] ?? 0);
} else {
    // Try employee code
    $sql = "SELECT id FROM tbl_employees WHERE employee_code = ? AND status_id = 1";
    $stmt = $cn->prepare($sql);
    $stmt->bind_param("s", $scan_data);
    $stmt->execute();
    if ($row = $stmt->get_result()->fetch_assoc()) {
        $employee_id = $row['id'];
    }
}

if (!$employee_id) {
    echo json_encode(['success' => false, 'message' => 'Employee not found']);
    exit;
}

// Verify employee
$sql = "SELECT id, name FROM tbl_employees WHERE id = ? AND status_id = 1";
$stmt = $cn->prepare($sql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Employee not active']);
    exit;
}

// Get current date/time
$current_date = date('Y-m-d');
$current_time = date('H:i:s');

// Determine next check type
$sql = "SELECT check_type_id FROM tbl_attendance_records 
        WHERE employee_id = ? AND date = ? AND deleted_at IS NULL 
        ORDER BY check_time DESC LIMIT 1";
$stmt = $cn->prepare($sql);
$stmt->bind_param("is", $employee_id, $current_date);
$stmt->execute();
$result = $stmt->get_result();

$check_type_id = 1; // Default to first check-in
if ($result->num_rows > 0) {
    $last = $result->fetch_assoc();
    $check_type_id = ($last['check_type_id'] % 4) + 1;
}

// Check duplicate
$sql = "SELECT id FROM tbl_attendance_records 
        WHERE employee_id = ? AND date = ? AND check_type_id = ? 
        AND TIMESTAMPDIFF(MINUTE, created_at, NOW()) < 5";
$stmt = $cn->prepare($sql);
$stmt->bind_param("isi", $employee_id, $current_date, $check_type_id);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Already checked ' . ($check_type_id % 2 == 0 ? 'out' : 'in') . ' recently'
    ]);
    exit;
}

// Create record
$uuid = bin2hex(random_bytes(16));
$sql = "INSERT INTO tbl_attendance_records 
        (uuid, employee_id, date, check_time, check_type_id, status_id, created_at) 
        VALUES (?, ?, ?, ?, ?, 1, NOW())";
$stmt = $cn->prepare($sql);
$stmt->bind_param("sissi", $uuid, $employee_id, $current_date, $current_time, $check_type_id);

if ($stmt->execute()) {
    $record_id = $stmt->insert_id;
    
    // Get employee name
    $sql = "SELECT name FROM tbl_employees WHERE id = ?";
    $stmt = $cn->prepare($sql);
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $employee_name = $stmt->get_result()->fetch_assoc()['name'] ?? 'Employee';
    
    // Get check type name
    $check_names = ['', 'Check-in', 'Lunch Break', 'Check-in', 'Check-out'];
    $check_name = $check_names[$check_type_id];
    
    // Get next action
    $next_type = ($check_type_id % 4) + 1;
    $next_action = $check_names[$next_type] ?? 'Next check';
    
    echo json_encode([
        'success' => true,
        'message' => 'Attendance recorded successfully',
        'data' => [
            'employee' => $employee_name,
            'check_type' => $check_name,
            'time' => $current_time,
            'date' => $current_date,
            'record_id' => $record_id,
            'next_action' => $next_action
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to record attendance']);
}
?>