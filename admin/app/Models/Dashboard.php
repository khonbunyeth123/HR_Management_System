<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Database;
use PDO;
use PDOException; 

class Dashboard
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function totalEmployees(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM tbl_employees WHERE deleted_at IS NULL")->fetchColumn();
    }

    public function activeEmployees(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) 
            FROM tbl_users 
            WHERE status_id = 1 AND deleted_at IS NULL;")->fetchColumn();
    }

    public function pendingLeaves(): int
    {
        return (int) $this->db->query("SELECT COUNT(*)
                FROM tbl_leave_applications
                WHERE status_id = 0 AND deleted_at IS NULL;")->fetchColumn();
    }

    public function onLeaveToday(): int
    {
        return (int) $this->db->query("SELECT COUNT(*)
            FROM tbl_leave_applications
            WHERE status_id = 1 
            AND CURDATE() BETWEEN start_date AND end_date
            AND deleted_at IS NULL
        ")->fetchColumn();
    }

    /**
     * Get summary stats - returns camelCase keys matching frontend expectations
     */
    public function getSummaryStats(): array
    {
        return [
            'total_employees' => $this->totalEmployees(),
            'active_employees' => $this->activeEmployees(),
            'pending_leaves' => $this->pendingLeaves(),
            'on_leave_today' => $this->onLeaveToday(),
        ];
    }

    public function departmentStats(): array
    {
        try {
            $sql = "
                SELECT 
                    department AS name,
                    COUNT(*) AS count
                FROM tbl_employees
                WHERE deleted_at IS NULL
                GROUP BY department
                ORDER BY count DESC
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate total for percentages
            $total = array_sum(array_column($departments, 'count'));
            
            // Add percentage to each department
            return array_map(function($dept) use ($total) {
                $dept['percentage'] = $total > 0 ? round(($dept['count'] / $total) * 100, 1) : 0;
                return $dept;
            }, $departments);

        } catch (PDOException $e) {
            error_log("Error fetching department stats: " . $e->getMessage());
            return [];
        }
    }

    public function recentLeaves(int $limit = 5): array
    {
        if ($limit <= 0 || $limit > 100) {
            $limit = 5;
        }

        $sql = "
            SELECT 
                la.id,
                la.uuid,
                COALESCE(e.full_name, CONCAT(COALESCE(e.first_name, ''), ' ', COALESCE(e.last_name, ''))) AS name,
                COALESCE(lt.name, 'Not Specified') AS type,
                DATE_FORMAT(la.start_date, '%M %d') AS start_date_formatted,
                DATE_FORMAT(la.end_date, '%M %d') AS end_date_formatted,
                CONCAT(DATE_FORMAT(la.start_date, '%M %d'), ' - ', DATE_FORMAT(la.end_date, '%M %d')) AS period,
                DATEDIFF(la.end_date, la.start_date) + 1 AS total_days,
                CASE 
                    WHEN la.status_id = 0 THEN 'pending'
                    WHEN la.status_id = 1 THEN 'approved'
                    WHEN la.status_id = 2 THEN 'rejected'
                    ELSE 'unknown'
                END AS status,
                la.status_id,
                la.reason,
                DATE_FORMAT(la.created_at, '%Y-%m-%d %H:%i:%s') AS created_at,
                e.username,
                e.id AS employee_id,
                lt.id AS leave_type_id
            FROM tbl_leave_applications la
            INNER JOIN tbl_employees e ON la.employee_id = e.id
            INNER JOIN tbl_leave_types lt ON la.leave_type_id = lt.id
            WHERE la.deleted_at IS NULL 
                AND e.deleted_at IS NULL
            ORDER BY la.created_at DESC
            LIMIT :limit
        ";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching recent leaves: " . $e->getMessage());
            return [];
        }
    }
}