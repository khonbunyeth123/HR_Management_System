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

// --- Read input from GET ---
$input = $_GET;

// Paging options
$page = isset($input['paging_options']['page']) ? (int) $input['paging_options']['page'] : 1;
$per_page = isset($input['paging_options']['per_page']) ? (int) $input['paging_options']['per_page'] : 10;
$offset = ($page - 1) * $per_page;

// Filters and sorts
$filters = $input['filters'] ?? [];
$sorts = $input['sorts'] ?? [];

// --- Build WHERE and ORDER BY ---
list($whereSQL, $params, $types) = buildSQLFilter($filters);
$orderSQL = buildSQLSort($sorts, "created_at"); // default sort

// --- Count total ---
$countSQL = "SELECT COUNT(*) AS total FROM tbl_users_audits WHERE $whereSQL";
$stmtCount = $cn->prepare($countSQL);
if (!empty($params)) {
    $stmtCount->bind_param($types, ...$params);
}
$stmtCount->execute();
$total = $stmtCount->get_result()->fetch_assoc()['total'] ?? 0;

// --- Fetch data ---
$sql = "
    SELECT 
        id,
        user_id,
        context,
        description,
        audit_type_id,
        user_agent,
        operator,
        ip,
        status_id,
        `order`,
        created_at,
        created_by,
        updated_at,
        updated_by
    FROM tbl_users_audits
    WHERE $whereSQL
    $orderSQL
    LIMIT ? OFFSET ?
";

$params[] = $per_page;
$params[] = $offset;
$types .= "ii";

$stmt = $cn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// --- Pagination info ---
$audit_data = [
    "user_audits" => $data,
];
$pagination = [
    "total" => (int) $total,
    "page" => $page,
    "per_page" => $per_page,
    "total_pages" => ceil($total / $per_page)
];

// --- Return response ---
jsonResponseWithPagination("User audit logs fetched successfully", $audit_data, $pagination);

$cn->close();
