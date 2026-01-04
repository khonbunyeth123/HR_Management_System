<?php

namespace App\Controllers\Api;

use App\Core\Database;

class DashboardController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function summary()
    {
        try {
            // Fetch total employees
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM tbl_employees");
            $totalEmployees = $stmt->fetch(\PDO::FETCH_ASSOC)['total'];

            // Fetch active employees
            $stmt = $this->db->query("SELECT COUNT(*) as active FROM users WHERE status = 'active'");
            $activeEmployees = $stmt->fetch(\PDO::FETCH_ASSOC)['active'];

            // Fetch pending leaves
            $stmt = $this->db->query("SELECT COUNT(*) as pending FROM leave_applications WHERE status = 'pending'");
            $pendingLeaves = $stmt->fetch(\PDO::FETCH_ASSOC)['pending'];

            // Fetch on leave today
            $stmt = $this->db->query("
                SELECT COUNT(*) as on_leave 
                FROM leave_applications 
                WHERE status = 'approved' 
                AND CURDATE() BETWEEN start_date AND end_date
            ");
            $onLeaveToday = $stmt->fetch(\PDO::FETCH_ASSOC)['on_leave'];

            echo json_encode([
                'success' => true,
                'data' => [
                    'total_employees' => (int)$totalEmployees,
                    'active_employees' => (int)$activeEmployees,
                    'pending_leaves' => (int)$pendingLeaves,
                    'on_leave_today' => (int)$onLeaveToday
                ]
            ]);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error fetching summary data',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function recentLeaves()
    {
        try {
            $stmt = $this->db->query("
                SELECT 
                    la.id,
                    CONCAT(e.first_name, ' ', e.last_name) as name,
                    lt.name as type,
                    la.status,
                    CONCAT(DATE_FORMAT(la.start_date, '%M %d'), ' - ', DATE_FORMAT(la.end_date, '%M %d')) as period
                FROM leave_applications la
                JOIN tbl_employees e ON la.employee_id = e.id
                JOIN tbl_leave_types lt ON la.leave_type_id = lt.id
                ORDER BY la.created_at DESC
                LIMIT 5
            ");

            $leaves = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'data' => $leaves
            ]);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error fetching recent leaves',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function department()
    {
        try {
            $totalStmt = $this->db->query("SELECT COUNT(*) as total FROM tbl_employees");
            $totalEmployees = $totalStmt->fetch(\PDO::FETCH_ASSOC)['total'];

            $stmt = $this->db->query("
                SELECT 
                    department as name,
                    COUNT(*) as count,
                    ROUND((COUNT(*) * 100.0 / $totalEmployees), 1) as percentage
                FROM tbl_employees
                GROUP BY department
                ORDER BY count DESC
            ");

            $departments = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'data' => $departments
            ]);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error fetching departments',
                'error' => $e->getMessage()
            ]);
        }
    }
}