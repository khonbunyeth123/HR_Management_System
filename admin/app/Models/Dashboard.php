<?php

namespace App\Models;

use PDO;
use App\Core\Database;

class Dashboard
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function totalEmployees(): int
    {
        return (int) $this->db
            ->query("SELECT COUNT(*) FROM tbl_employees WHERE deleted_at IS NULL")
            ->fetchColumn();
    }

    public function activeEmployees(): int
    {
        return (int) $this->db
            ->query("SELECT COUNT(*) FROM tbl_employees WHERE status_id = 1 AND deleted_at IS NULL")
            ->fetchColumn();
    }

    public function pendingLeaves(): int
    {
        return (int) $this->db
            ->query("SELECT COUNT(*) FROM tbl_leave_applications WHERE status_id = 0 AND deleted_at IS NULL")
            ->fetchColumn();
    }

    public function onLeaveToday(): int
    {
        $today = date('Y-m-d');

        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT employee_id)
            FROM tbl_leave_applications
            WHERE :today BETWEEN start_date AND end_date
              AND status_id = 1
              AND deleted_at IS NULL
        ");
        $stmt->execute(['today' => $today]);

        return (int) $stmt->fetchColumn();
    }

    public function departmentStats(): array
    {
        $sql = "
            SELECT 
                department AS name,
                COUNT(id) AS count,
                ROUND(
                    (COUNT(id) * 100.0 /
                    (SELECT COUNT(*) FROM tbl_employees WHERE deleted_at IS NULL)), 1
                ) AS percentage
            FROM tbl_employees
            WHERE deleted_at IS NULL
            GROUP BY department
            ORDER BY count DESC
        ";

        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function recentLeaves(int $limit): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                e.full_name,
                la.leave_type,
                la.start_date,
                la.end_date,
                la.status_id
            FROM tbl_leave_applications la
            JOIN tbl_employees e ON la.employee_id = e.id
            WHERE la.deleted_at IS NULL
            ORDER BY la.created_at DESC
            LIMIT :limit
        ");

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return array_map(function ($row) {
            return [
                'name'   => $row['full_name'],
                'type'   => ucfirst($row['leave_type']),
                'period' => $row['start_date'] . ' to ' . $row['end_date'],
                'status' => $row['status_id'] == 0 ? 'Pending'
                          : ($row['status_id'] == 1 ? 'Approved' : 'Rejected')
            ];
        }, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}
