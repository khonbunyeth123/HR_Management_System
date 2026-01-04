<?php
header('Content-Type: application/json');
session_start();

// Include database connection
include(__DIR__ . "/../../action/db/cn.php");

// Response functions (since the external one isn't working)
function jsonResponse($message, $data = [], $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode([
        'success' => $statusCode === 200,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

function jsonErrorResponse($message, $data = [], $statusCode = 400) {
    jsonResponse($message, $data, $statusCode);
}

// Database connection check
if (!isset($cn)) {
    jsonErrorResponse("Database connection not initialized", [], 500);
}

$cn->set_charset("utf8");
if ($cn->connect_error) {
    jsonErrorResponse("Connection failed: " . $cn->connect_error, [], 500);
}

// Check authentication
if (!isset($_SESSION['uid'])) {
    jsonErrorResponse("Unauthorized - Please log in", [], 401);
}

// --- Read JSON body ---
$input = json_decode(file_get_contents("php://input"), true);
if (!$input) {
    jsonErrorResponse("Invalid or missing JSON body", [], 400);
}

// --- Validate ID ---
if (empty($input['id'])) {
    jsonErrorResponse("Missing employee ID", [], 400);
}

$employee_id = (int) $input['id'];

// --- Check if employee exists and get current data ---
$check_sql = "SELECT first_name, last_name FROM tbl_employees WHERE id = ? AND deleted_at IS NULL";
$check_stmt = $cn->prepare($check_sql);
if (!$check_stmt) {
    jsonErrorResponse("Check prepare failed: " . $cn->error, [], 500);
}

$check_stmt->bind_param("i", $employee_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    jsonErrorResponse("Employee not found or already deleted", [], 404);
}

$current_employee = $result->fetch_assoc();
$check_stmt->close();

// --- Determine first_name and last_name for full_name generation ---
$first_name = isset($input['first_name']) ? trim($input['first_name']) : $current_employee['first_name'];
$last_name = isset($input['last_name']) ? trim($input['last_name']) : $current_employee['last_name'];

// --- Auto-generate full_name if first_name or last_name is being updated ---
if (isset($input['first_name']) || isset($input['last_name'])) {
    $input['full_name'] = trim($first_name . ' ' . $last_name);
}

// --- Optional fields - based on your table structure ---
$fields = [
    'first_name', 
    'last_name', 
    'full_name', 
    'position', 
    'department',
    'date_hired', 
    'status_id'
];
$setSQL = [];
$params = [];
$types = '';

foreach ($fields as $field) {
    if (isset($input[$field]) && $input[$field] !== '') {
        $setSQL[] = "$field = ?";
        $params[] = $input[$field];
        
        // Determine parameter type
        if (in_array($field, ['status_id'])) {
            $types .= 'i'; // integer
        } else {
            $types .= 's'; // string
        }
    }
}

if (empty($setSQL)) {
    jsonErrorResponse("No fields to update", [], 400);
}

// --- Add updated_at and updated_by ---
$setSQL[] = "updated_at = NOW()";
$setSQL[] = "updated_by = ?";
$params[] = $_SESSION['uid'];
$types .= 'i';

// --- Final SQL ---
$sql = "UPDATE tbl_employees SET " . implode(", ", $setSQL) . " WHERE id = ? AND deleted_at IS NULL";
$params[] = $employee_id;
$types .= 'i';

// Debug output
error_log("Update SQL: " . $sql);
error_log("Parameter types: " . $types);
error_log("Parameters: " . print_r($params, true));

$stmt = $cn->prepare($sql);
if (!$stmt) {
    jsonErrorResponse("SQL prepare failed: " . $cn->error, [], 500);
}

$stmt->bind_param($types, ...$params);

if (!$stmt->execute()) {
    jsonErrorResponse("Failed to update employee: " . $stmt->error, [], 500);
}

// Check if update actually occurred
if ($stmt->affected_rows === 0) {
    jsonResponse("No changes made - data may be identical", [], 200);
}

jsonResponse("Employee updated successfully", [
    "employee_id" => $employee_id,
    "affected_rows" => $stmt->affected_rows
]);

$stmt->close();
$cn->close();
?>