<?php
header('Content-Type: application/json');
session_start();

include(__DIR__ . "/../../action/db/cn.php");
include(__DIR__ . "/../../utils/response.php");
include(__DIR__ . "/../../utils/sql_helper.php");

// Only allow POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonErrorResponse("Method not allowed. Use POST.", [], 405);
}

if (!isset($cn)) {
    jsonErrorResponse("Database connection not initialized", [], 500);
}

$cn->set_charset("utf8");
if ($cn->connect_error) {
    jsonErrorResponse("Connection failed: " . $cn->connect_error, [], 500);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    jsonErrorResponse("Invalid JSON input", [], 400);
}

// ============================================
// MOBILE QR SUPPORT
// ============================================
$attendance_data = [];

if (isset($input['qr_data'])) {
    // Mobile QR scanning
    $employee_id = extractEmployeeId($input['qr_data']);
    
    if (!$employee_id || !employeeExists($employee_id)) {
        jsonErrorResponse("Invalid QR code or employee not found", [], 404);
    }
    
    $current_date = date('Y-m-d');
    $current_time = date('H:i:s');
    $check_type_id = determineCheckType($employee_id, $current_date);
    
    if (isDuplicateEntry($employee_id, $current_date, $check_type_id)) {
        jsonErrorResponse("Already checked " . ($check_type_id % 2 == 0 ? 'out' : 'in') . " recently", [], 409);
    }
    
    $attendance_data = [
        'employee_id' => $employee_id,
        'date' => $current_date,
        'check_time' => $current_time,
        'check_type_id' => $check_type_id,
        'source' => 'mobile_qr'
    ];
} else {
    // Original web form data
    $attendance_data = $input;
}

// ============================================
// VALIDATION
// ============================================
$required_fields = ['employee_id', 'date', 'check_time', 'check_type_id'];
foreach ($required_fields as $field) {
    if (!isset($attendance_data[$field]) || empty($attendance_data[$field])) {
        jsonErrorResponse("Missing required field: $field", [], 400);
    }
}

// ============================================
// CREATE RECORD
// ============================================
$uuid = bin2hex(random_bytes(16));
$status_id = $attendance_data['status_id'] ?? 1;
$created_by = $_SESSION['user_id'] ?? $attendance_data['employee_id'] ?? null;

$sql = "INSERT INTO tbl_attendance_records 
        (uuid, employee_id, date, check_time, check_type_id, status_id, created_at, created_by) 
        VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)";

$stmt = $cn->prepare($sql);
if (!$stmt) {
    jsonErrorResponse("Prepare failed: " . $cn->error, [], 500);
}

$stmt->bind_param(
    "sisssii",
    $uuid,
    $attendance_data['employee_id'],
    $attendance_data['date'],
    $attendance_data['check_time'],
    $attendance_data['check_type_id'],
    $status_id,
    $created_by
);

if ($stmt->execute()) {
    $record_id = $stmt->insert_id;
    
    // Fetch the created record
    $fetch_sql = "SELECT ar.*, ct.name as check_type_name 
                  FROM tbl_attendance_records ar
                  LEFT JOIN tbl_check_types ct ON ar.check_type_id = ct.id
                  WHERE ar.id = ?";
    
    $fetch_stmt = $cn->prepare($fetch_sql);
    $fetch_stmt->bind_param("i", $record_id);
    $fetch_stmt->execute();
    $record = $fetch_stmt->get_result()->fetch_assoc();
    
    // Mobile-friendly response if from mobile
    if (isset($input['qr_data'])) {
        $response = [
            'success' => true,
            'status_code' => 201,
            'message' => getSuccessMessage($record['check_type_id']),
            'data' => [
                'employee' => getEmployeeInfo($record['employee_id']),
                'check_type' => $record['check_type_name'],
                'time' => $record['check_time'],
                'date' => $record['date'],
                'next_action' => getNextAction($record['employee_id'], $record['date'])
            ]
        ];
        echo json_encode($response);
    } else {
        jsonResponse("Attendance record created successfully", $record, 201);
    }
} else {
    jsonErrorResponse("Failed to create record: " . $stmt->error, [], 500);
}

$stmt->close();
$cn->close();

// ============================================
// HELPER FUNCTIONS
// ============================================
function extractEmployeeId($qr_data) {
    if (is_numeric($qr_data)) return (int)$qr_data;
    if (strpos($qr_data, 'EMP:') === 0) {
        $parts = explode(':', $qr_data);
        return isset($parts[1]) ? (int)$parts[1] : null;
    }
    if (strpos($qr_data, '{') === 0) {
        $data = json_decode($qr_data, true);
        return $data['employee_id'] ?? null;
    }
    return null;
}

function employeeExists($employee_id) {
    global $cn;
    $sql = "SELECT id FROM tbl_employees WHERE id = ? AND status_id = 1 AND deleted_at IS NULL";
    $stmt = $cn->prepare($sql);
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

function determineCheckType($employee_id, $date) {
    global $cn;
    $sql = "SELECT check_type_id FROM tbl_attendance_records 
            WHERE employee_id = ? AND date = ? AND deleted_at IS NULL 
            ORDER BY check_time DESC LIMIT 1";
    $stmt = $cn->prepare($sql);
    $stmt->bind_param("is", $employee_id, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $last = $result->fetch_assoc();
        return ($last['check_type_id'] % 4) + 1;
    }
    
    $hour = date('H');
    if ($hour < 12) return 1;
    if ($hour < 13) return 2;
    if ($hour < 17) return 3;
    return 4;
}

function isDuplicateEntry($employee_id, $date, $check_type_id) {
    global $cn;
    $sql = "SELECT id FROM tbl_attendance_records 
            WHERE employee_id = ? AND date = ? AND check_type_id = ? 
            AND deleted_at IS NULL 
            AND TIMESTAMPDIFF(MINUTE, created_at, NOW()) < 5";
    $stmt = $cn->prepare($sql);
    $stmt->bind_param("isi", $employee_id, $date, $check_type_id);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

function getSuccessMessage($check_type_id) {
    $messages = [
        1 => 'Morning check-in successful! Have a great day!',
        2 => 'Lunch break started. Enjoy your meal!',
        3 => 'Welcome back from lunch!',
        4 => 'Check-out successful. See you tomorrow!'
    ];
    return $messages[$check_type_id] ?? 'Attendance recorded successfully';
}

function getEmployeeInfo($employee_id) {
    global $cn;
    $sql = "SELECT name, employee_code FROM tbl_employees WHERE id = ?";
    $stmt = $cn->prepare($sql);
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc() ?: ['name' => 'Unknown'];
}

function getNextAction($employee_id, $date) {
    global $cn;
    $sql = "SELECT check_type_id FROM tbl_attendance_records 
            WHERE employee_id = ? AND date = ? AND deleted_at IS NULL 
            ORDER BY check_time DESC LIMIT 1";
    $stmt = $cn->prepare($sql);
    $stmt->bind_param("is", $employee_id, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $last = $result->fetch_assoc();
        $next_type = ($last['check_type_id'] % 4) + 1;
        $actions = [1 => 'Check-in', 2 => 'Lunch break', 3 => 'Check-in', 4 => 'Check-out'];
        return $actions[$next_type] ?? 'Next check';
    }
    return 'First check-in';
}
?>