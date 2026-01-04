<?php
header('Content-Type: application/json');
session_start();

include(__DIR__ . "/../../action/db/cn.php");
include(__DIR__ . "/../../utils/response.php");

if (!isset($cn)) {
    jsonErrorResponse("Database connection not initialized", [], 500);
}

$cn->set_charset("utf8");

if ($cn->connect_error) {
    jsonErrorResponse("Connection failed: " . $cn->connect_error, [], 500);
}

// --- Read JSON body ---
$input = json_decode(file_get_contents("php://input"), true);
if (!$input) {
    jsonErrorResponse("Invalid or missing JSON body", [], 400);
}

// --- Validate required fields ---
$required_fields = ['username', 'first_name', 'last_name', 'position', 'department', 'date_hired'];
foreach ($required_fields as $field) {
    if (empty($input[$field])) {
        jsonErrorResponse("Missing required field: $field", [], 400);
    }
}

// --- Prepare data ---
$uuid = bin2hex(random_bytes(16));
$full_name = trim($input['first_name'] . ' ' . $input['last_name']);
$status_id = isset($input['status_id']) ? (int)$input['status_id'] : 1;
$user_id = isset($input['user_id']) ? (int)$input['user_id'] : null;
$created_by = $_SESSION['uid'] ?? null;

// --- 🧠 Fix date_hired formatting ---
$date_hired = trim($input['date_hired']);

// If only year is provided (e.g. "2025"), make it a valid date "2025-01-01"
if (preg_match('/^\d{4}$/', $date_hired)) {
    $date_hired .= '-01-01';
}

// If date format is invalid, throw error
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_hired)) {
    jsonErrorResponse("Invalid date format for date_hired. Expected YYYY-MM-DD.", [], 400);
}

// --- SQL ---
$sql = "INSERT INTO tbl_employees 
        (uuid, user_id, username, first_name, last_name, full_name, position, department, date_hired, status_id, created_at, created_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";

$stmt = $cn->prepare($sql);
if (!$stmt) {
    jsonErrorResponse("SQL prepare failed: " . $cn->error, [], 500);
}

// ✅ FIXED: 11 parameters with correct type string "sisssssssii"
$stmt->bind_param(
    "sisssssssii",  // Changed to 11 characters: s-i-s-s-s-s-s-s-s-i-i
    $uuid,          // s - string
    $user_id,       // i - integer (can be null, but bind_param handles it)
    $input['username'], // s - string
    $input['first_name'], // s - string
    $input['last_name'],  // s - string
    $full_name,     // s - string
    $input['position'],   // s - string
    $input['department'], // s - string
    $date_hired,    // s - string (date)
    $status_id,     // i - integer
    $created_by     // i - integer
);

if (!$stmt->execute()) {
    jsonErrorResponse("Failed to insert employee: " . $stmt->error, [], 500);
}

jsonResponse("Employee created successfully", [
    "id" => $stmt->insert_id,
    "uuid" => $uuid,
    "full_name" => $full_name
]);

$stmt->close();
$cn->close();
?>