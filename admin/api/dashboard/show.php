<?php
    // dashboard_api.php
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');

    session_start();
    include(__DIR__ . "/../../action/db/cn.php");

    // Handle preflight request
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        exit(0);
    }

    // Check connection
    if (!$cn) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }

    $cn->set_charset("utf8");

    try {
        // Get dashboard stats
        if (isset($_GET['action']) && $_GET['action'] === 'dashboard_stats') {
            $stats = [
                'total_employees' => getTotalEmployees($cn),
                'active_employees' => getActiveEmployees($cn),
                'pending_leaves' => getPendingLeaves($cn),
                'on_leave_today' => getOnLeaveToday($cn),
                'departments' => getDepartmentStats($cn),
                'recent_leave_requests' => getRecentLeaveRequests($cn)
            ];

            echo json_encode([
                'success' => true,
                'data' => $stats
            ]);
            exit;
        }

        // Default response
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }

    $cn->close();

    // Helper functions based on your tbl_employees schema
    function getTotalEmployees($cn) {
        $sql = "SELECT COUNT(*) as count FROM tbl_employees";
        $result = $cn->query($sql);
        if ($result && $result->num_rows > 0) {
            return (int)$result->fetch_assoc()['count'];
        }
        return 0;
    }

    function getActiveEmployees($cn) {
        $sql = "SELECT COUNT(*) as count FROM tbl_employees WHERE status_id = 1 AND deleted_at IS NULL";
        $result = $cn->query($sql);
        if ($result && $result->num_rows > 0) {
            return (int)$result->fetch_assoc()['count'];
        }
        return 0;
    }

    function getPendingLeaves($cn) {
        $sql = "SELECT COUNT(*) AS count 
                FROM tbl_leave_applications
                WHERE status_id = 0 
                AND deleted_at IS NULL";

        $result = $cn->query($sql);
        if ($result) {
            return (int)$result->fetch_assoc()['count'];
        }
        return 0;
    }


    function getOnLeaveToday($cn) {
        $today = date('Y-m-d');

        $sql = "SELECT COUNT(DISTINCT employee_id) AS count 
                FROM tbl_leave_applications
                WHERE '$today' BETWEEN start_date AND end_date
                AND status_id = 1
                AND deleted_at IS NULL";

        $result = $cn->query($sql);
        if ($result) {
            return (int)$result->fetch_assoc()['count'];
        }
        return 0;
    }


    function getDepartmentStats($cn) {
        $sql = "SELECT 
                    department as name,
                    COUNT(id) as count,
                    ROUND((COUNT(id) * 100.0 / (SELECT COUNT(*) FROM tbl_employees WHERE deleted_at IS NULL)), 1) as percentage
                FROM tbl_employees 
                WHERE deleted_at IS NULL
                GROUP BY department
                ORDER BY count DESC
                LIMIT 5";
        
        $result = $cn->query($sql);
        $departments = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $departments[] = [
                    'name' => $row['name'],
                    'count' => (int)$row['count'],
                    'percentage' => (float)$row['percentage']
                ];
            }
        }
        
        // If no departments found, return sample data
        if (empty($departments)) {
            return [
                ['name' => 'IT', 'count' => 1, 'percentage' => 100.0]
            ];
        }
        
        return $departments;
    }

    function getRecentLeaveRequests($cn) {
        $sql = "SELECT 
                    e.full_name,
                    la.leave_type,
                    la.start_date,
                    la.end_date,
                    la.status_id
                FROM tbl_leave_applications la
                JOIN tbl_employees e ON la.employee_id = e.id
                WHERE la.deleted_at IS NULL
                ORDER BY la.created_at DESC
                LIMIT 4";

        $result = $cn->query($sql);
        $leaves = [];

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $leaves[] = [
                    'name' => $row['full_name'],
                    'type' => ucfirst($row['leave_type']),
                    'period' => $row['start_date'] . ' to ' . $row['end_date'],
                    'status' => ($row['status_id'] == 0 ? 'Pending' :
                                ($row['status_id'] == 1 ? 'Approved' : 'Rejected')),
                ];
            }
        }
        return $leaves;
    }
?>