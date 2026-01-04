<?php
header('Content-Type: application/json');
session_start();

include(__DIR__ . "/../../action/db/cn.php");
include(__DIR__ . "/../../utils/response.php");
include(__DIR__ . "/../../utils/sql_helper.php");

if (!isset($cn)) {
    jsonErrorResponse("Database connection not initialized", [], 500);
    exit;
}

$cn->set_charset("utf8");
if ($cn->connect_error) {
    jsonErrorResponse("Connection failed: " . $cn->connect_error, [], 500);
    exit;
}

// --- Read input from GET ---
$input = $_GET;

// --- Paging options ---
$page = isset($input['paging_options']['page']) ? (int)$input['paging_options']['page'] : 1;
$per_page = isset($input['paging_options']['per_page']) ? (int)$input['paging_options']['per_page'] : 10;
$offset = ($page - 1) * $per_page;

// --- Filters and sorts ---
$filters = $input['filters'] ?? [];
$sorts = $input['sorts'] ?? [];

// --- Build WHERE and ORDER BY ---
list($whereSQL, $params, $types) = buildSQLFilter($filters);
$whereSQL = $whereSQL ?: "1";

$orderSQL = buildSQLSort($sorts, "created_at");

// --- Count only NON-DELETED employees ---
$countSQL = "SELECT COUNT(*) AS total FROM tbl_employees WHERE $whereSQL AND deleted_at IS NULL";
$stmtCount = $cn->prepare($countSQL);
if (!empty($params)) {
    $stmtCount->bind_param($types, ...$params);
}
$stmtCount->execute();
$resultCount = $stmtCount->get_result();
$total = $resultCount->fetch_assoc()['total'] ?? 0;
$stmtCount->close();

// --- Fetch only NON-DELETED employees ---
$sql = "SELECT 
            id, uuid, user_id, username, 
            first_name, last_name,
            CONCAT(first_name, ' ', last_name) as full_name,
            position, department, date_hired, status_id,
            created_at, updated_at
        FROM tbl_employees 
        WHERE $whereSQL AND deleted_at IS NULL 
        $orderSQL 
        LIMIT ? OFFSET ?";

$paramsWithLimit = $params;
$paramsWithLimit[] = $per_page;
$paramsWithLimit[] = $offset;
$typesWithLimit = $types . "ii";

$stmt = $cn->prepare($sql);
$stmt->bind_param($typesWithLimit, ...$paramsWithLimit);
$stmt->execute();
$employees = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// --- Match frontend expected structure ---
$response_data = [
    "employees" => $employees,
    "total_count" => (int)$total
];

$pagination = [
    "total" => (int)$total,
    "page" => $page,
    "per_page" => $per_page,
    "total_pages" => ceil($total / $per_page)
];

// --- Return response ---
jsonResponseWithPagination("Employees fetched successfully", $response_data, $pagination);

$cn->close();
?>