<?php
header('Content-Type: application/json');
include(__DIR__ . "/../../action/db/cn.php");

// Paging
$page = (int)($_GET['paging_options']['page'] ?? 1);
$per_page = (int)($_GET['paging_options']['per_page'] ?? 5);
$offset = ($page-1)*$per_page;

// Filters
$employee = $_GET['filters']['employee_name'] ?? '';
$leaveType = $_GET['filters']['leave_type'] ?? '';
$status = $_GET['filters']['status_id'] ?? '';

// Build WHERE
$where = "1=1";
$params = [];
$types = '';
if($employee){ $where.=" AND e.full_name LIKE ?"; $params[]="%$employee%"; $types.="s"; }
if($leaveType){ $where.=" AND t.name=?"; $params[]=$leaveType; $types.="s"; }
if($status!==''){ $where.=" AND l.status_id=?"; $params[]=$status; $types.="i"; }

// Count total
$stmt = $cn->prepare("SELECT COUNT(*) AS total FROM tbl_leave_applications l INNER JOIN tbl_employees e ON l.employee_id=e.id INNER JOIN tbl_leave_types t ON l.leave_type_id=t.id WHERE $where");
if(!empty($params)) $stmt->bind_param($types,...$params);
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

// Fetch data
$sql = "SELECT l.uuid, e.full_name AS employee_name, t.name AS leave_type, l.start_date, l.end_date, l.reason, l.status_id, l.created_at 
        FROM tbl_leave_applications l 
        INNER JOIN tbl_employees e ON l.employee_id=e.id
        INNER JOIN tbl_leave_types t ON l.leave_type_id=t.id
        WHERE $where
        ORDER BY l.start_date DESC
        LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;
$types.="ii";

$stmt = $cn->prepare($sql);
if(!empty($params)) $stmt->bind_param($types,...$params);
$stmt->execute();
$data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch all leave types for filter
$leave_types_result = $cn->query("SELECT DISTINCT name FROM tbl_leave_types ORDER BY name ASC");
$leave_types = [];
while($row = $leave_types_result->fetch_assoc()) $leave_types[] = $row['name'];

// Response
echo json_encode([
    "success"=>true,
    "message"=>"Leave applications fetched successfully",
    "data"=>[
        "leave_applications"=>$data,
        "leave_types"=>$leave_types
    ],
    "pagination"=>[
        "total"=>$total,
        "page"=>$page,
        "per_page"=>$per_page,
        "total_pages"=>ceil($total/$per_page)
    ]
]);
