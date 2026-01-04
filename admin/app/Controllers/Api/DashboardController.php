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
            $stmt = $this->db->prepare("
                SELECT
                    (SELECT COUNT(*) FROM tbl_employees) as total_employees,
                    (SELECT COUNT(*) FROM tbl_users WHERE status_id = 1) as active_employees,
                    (SELECT COUNT(*) FROM tbl_leave_applications WHERE status_id = 0) as pending_leaves,
                    (SELECT COUNT(*) FROM tbl_leave_applications WHERE status_id = 1 AND CURDATE() BETWEEN start_date AND end_date) as on_leave_today
            ");
            $stmt->execute();
            $data = $stmt->fetch(\PDO::FETCH_ASSOC);
            $data['total_employees'] = (int)($data['total_employees'] ?? 0);
            $data['active_employees'] = (int)($data['active_employees'] ?? 0);
            $data['pending_leaves'] = (int)($data['pending_leaves'] ?? 0);
            $data['on_leave_today'] = (int)($data['on_leave_today'] ?? 0);
            echo json_encode(['success' => true, 'data' => $data]);
        } catch (\PDOException $e) {
            error_log("DB Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
    }

    public function recentLeaves()
    {
        try {
            $stmt = $this->db->prepare("
                SELECT la.id, CONCAT(e.first_name, ' ', e.last_name) as name, lt.name as type, 
                CASE WHEN la.status_id = 0 THEN 'pending' WHEN la.status_id = 1 THEN 'approved' WHEN la.status_id = 2 THEN 'rejected' ELSE 'unknown' END as status,
                CONCAT(DATE_FORMAT(la.start_date, '%M %d'), ' - ', DATE_FORMAT(la.end_date, '%M %d')) as period
                FROM tbl_leave_applications la
                JOIN tbl_employees e ON la.employee_id = e.id
                JOIN tbl_leave_types lt ON la.leave_type_id = lt.id
                WHERE la.deleted_at IS NULL ORDER BY la.created_at DESC LIMIT 5
            ");
            $stmt->execute();
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll(\PDO::FETCH_ASSOC)]);
        } catch (\PDOException $e) {
            error_log("DB Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
    }

    public function department()
    {
        try {
            $totalStmt = $this->db->prepare("SELECT COUNT(*) as total FROM tbl_employees WHERE deleted_at IS NULL");
            $totalStmt->execute();
            $totalEmployees = $totalStmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0;
            $stmt = $this->db->prepare("SELECT department as name, COUNT(*) as count, ROUND((COUNT(*) * 100.0 / :total), 1) as percentage FROM tbl_employees WHERE deleted_at IS NULL GROUP BY department ORDER BY count DESC");
            $stmt->bindParam(':total', $totalEmployees, \PDO::PARAM_INT);
            $stmt->execute();
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll(\PDO::FETCH_ASSOC)]);
        } catch (\PDOException $e) {
            error_log("DB Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
    }
}
