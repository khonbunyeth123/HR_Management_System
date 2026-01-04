<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include(__DIR__ . "/../../action/db/cn.php");

$employee_id = $_GET['employee_id'] ?? null;

if (!$employee_id) {
    echo json_encode(['success' => false, 'message' => 'Employee ID required']);
    exit;
}

// Get employee info
$sql = "SELECT id, name, employee_code FROM tbl_employees WHERE id = ? AND status_id = 1";
$stmt = $cn->prepare($sql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();

if (!$employee) {
    echo json_encode(['success' => false, 'message' => 'Employee not found']);
    exit;
}

// Get today's attendance
$sql = "SELECT ar.check_time, ar.check_type_id, ct.name as check_type_name, ct.standard_time
        FROM tbl_attendance_records ar
        LEFT JOIN tbl_check_types ct ON ar.check_type_id = ct.id
        WHERE ar.employee_id = ? AND ar.date = CURDATE() 
        AND ar.deleted_at IS NULL 
        ORDER BY ar.check_time";
        
$stmt = $cn->prepare($sql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$today = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate status
foreach ($today as &$record) {
    if ($record['standard_time']) {
        $check = strtotime($record['check_time']);
        $standard = strtotime($record['standard_time']);
        $diff = ($check - $standard) / 60;
        
        if ($diff > 5) {
            $record['status'] = 'late';
            $record['minutes'] = round($diff);
        } elseif ($diff < -5) {
            $record['status'] = 'early';
            $record['minutes'] = round(abs($diff));
        } else {
            $record['status'] = 'on_time';
        }
    }
}

// Get monthly summary
$sql = "SELECT 
    COUNT(DISTINCT date) as working_days,
    SUM(CASE WHEN check_type_id IN (1,3) THEN 1 ELSE 0 END) as checkins,
    SUM(CASE WHEN check_type_id IN (2,4) THEN 1 ELSE 0 END) as checkouts
    FROM tbl_attendance_records 
    WHERE employee_id = ? 
    AND MONTH(date) = MONTH(CURDATE())
    AND YEAR(date) = YEAR(CURDATE())
    AND deleted_at IS NULL";
    
$stmt = $cn->prepare($sql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$monthly = $stmt->get_result()->fetch_assoc();

// Determine next action
$next_action = 'Check-in (Morning)';
if (!empty($today)) {
    $last = end($today);
    $next_type = ($last['check_type_id'] % 4) + 1;
    $actions = [1 => 'Check-in', 2 => 'Lunch Break', 3 => 'Check-in', 4 => 'Check-out'];
    $next_action = $actions[$next_type] ?? 'Next check';
}

echo json_encode([
    'success' => true,
    'employee' => $employee,
    'today' => [
        'records' => $today,
        'total' => count($today),
        'next_action' => $next_action
    ],
    'monthly' => $monthly,
    'current' => [
        'date' => date('Y-m-d'),
        'time' => date('H:i:s'),
        'day' => date('l')
    ]
]);
?>