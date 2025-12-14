<?php
header('Content-Type: application/json');
session_start();

include(__DIR__ . "/../../action/db/cn.php");
include(__DIR__ . "/../../utils/response.php");
include(__DIR__ . "/../../utils/sql_helper.php");

if (!isset($cn)) {
    jsonErrorResponse("Database connection not initialized", [], 500);
}

$cn->set_charset("utf8");
if ($cn->connect_error) {
    jsonErrorResponse("Connection failed: " . $cn->connect_error, [], 500);
}

// Read input
$input = $_GET;

// Paging
$page = isset($input['paging_options']['page']) ? (int) $input['paging_options']['page'] : 1;
$per_page = isset($input['paging_options']['per_page']) ? (int) $input['paging_options']['per_page'] : 10;
$offset = ($page - 1) * $per_page;

// Filters & sorts
$filters = $input['filters'] ?? [];
$sorts = $input['sorts'] ?? [];

list($whereSQL, $params, $types) = buildSQLFilter($filters);
if (empty($whereSQL)) $whereSQL = "1=1"; // Ensure SQL is valid if no filters
$orderSQL = buildSQLSort($sorts, "l.start_date");

// Count total
$countSQL = "SELECT COUNT(*) AS total 
             FROM tbl_leave_applications l
             INNER JOIN tbl_employees e ON l.employee_id = e.id
             INNER JOIN tbl_leave_types t ON l.leave_type_id = t.id
             WHERE $whereSQL";

$stmtCount = $cn->prepare($countSQL);
if (!empty($params))
    $stmtCount->bind_param($types, ...$params);
$stmtCount->execute();
$total = $stmtCount->get_result()->fetch_assoc()['total'] ?? 0;

// Fetch data
$sql = "SELECT 
            l.uuid,
            e.full_name AS employee_name,
            t.name AS leave_type,
            l.start_date,
            l.end_date,
            l.reason,
            l.status_id,
            l.approved_by,
            l.created_at,
            l.created_by
        FROM tbl_leave_applications l
        INNER JOIN tbl_employees e ON l.employee_id = e.id
        INNER JOIN tbl_leave_types t ON l.leave_type_id = t.id
        WHERE $whereSQL
        $orderSQL
        LIMIT ? OFFSET ?";

$params[] = $per_page;
$params[] = $offset;
$types .= "ii";

$stmt = $cn->prepare($sql);
if (!empty($params)) $stmt->bind_param($types, ...$params);
$stmt->execute();
$data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Pagination info
$response_data = [
    "leave_applications" => $data,
];
$pagination = [
    "total" => (int) $total,
    "page" => $page,
    "per_page" => $per_page,
    "total_pages" => ceil($total / $per_page)
];

jsonResponseWithPagination("Leave applications fetched successfully", $response_data, $pagination);
$cn->close();
