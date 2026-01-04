<?php
header('Content-Type: application/json');
session_start();

include(__DIR__ . "/../../action/db/cn.php");
include(__DIR__ . "/../../utils/response.php");
include(__DIR__ . "/../../utils/sql_helper.php");

// --- Check DB connection ---
if (!isset($cn)) {
    jsonErrorResponse("Database connection not initialized", [], 500);
}

$cn->set_charset("utf8");
if ($cn->connect_error) {
    jsonErrorResponse("Connection failed: " . $cn->connect_error, [], 500);
}

// --- Read input from GET ---
$input = $_GET;

// Paging options
$page = isset($input['paging_options']['page']) ? (int)$input['paging_options']['page'] : 1;
$per_page = isset($input['paging_options']['per_page']) ? (int)$input['paging_options']['per_page'] : 10;
$offset = ($page - 1) * $per_page;

// Filters
$filters = $input['filters'] ?? [];

// --- Force today date filter ---
$filters['date'] = date("Y-m-d");

// --- Build WHERE & SORT ---
list($whereSQL, $params, $types) = buildSQLFilter($filters);

// If no filter returned → avoid SQL error
if (trim($whereSQL) === "" || $whereSQL === null) {
    $whereSQL = "1"; // always true
}

$orderSQL = "ORDER BY check_time DESC"; // default order for today

// --- Count total ---
$countSQL = "SELECT COUNT(*) AS total FROM tbl_attendance_records WHERE $whereSQL";
$stmtCount = $cn->prepare($countSQL);

if (!empty($params)) {
    $stmtCount->bind_param($types, ...$params);
}

$stmtCount->execute();
$total = $stmtCount->get_result()->fetch_assoc()['total'] ?? 0;

// --- Fetch data ---
$sql = "SELECT * FROM tbl_attendance_records 
        WHERE $whereSQL 
        $orderSQL 
        LIMIT ? OFFSET ?";

$params2 = $params; // clone params
$params2[] = $per_page;
$params2[] = $offset;
$types2 = $types . "ii";

$stmt = $cn->prepare($sql);
$stmt->bind_param($types2, ...$params2);
$stmt->execute();
$data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// --- Pagination info ---
$attendance_data = [
    "attendance_records" => $data
];

$pagination = [
    "total" => (int)$total,
    "page" => $page,
    "per_page" => $per_page,
    "total_pages" => ceil($total / $per_page)
];

// --- Return response ---
jsonResponseWithPagination("Today's attendance fetched successfully", $attendance_data, $pagination);

$cn->close();
