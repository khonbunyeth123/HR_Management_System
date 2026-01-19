<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Leave {
    private PDO $db;

    public function __construct()
    {
        try {
            $this->db = Database::getInstance()->getConnection();

            if (!$this->db) {
                throw new \Exception('Failed to establish database connection');
            }
        } catch (\Exception $e) {
            error_log("Leave Model - Database Connection Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get all leave applications with filters and pagination
     */
    public function getAll(array $filters, int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;

        $where = "1=1";
        $params = [];

        // Filters
        if (!empty($filters['employee_name'])) {
            $where .= " AND e.full_name LIKE :employee_name";
            $params[':employee_name'] = "%" . $filters['employee_name'] . "%";
        }

        if (!empty($filters['leave_type'])) {
            $where .= " AND t.name = :leave_type";
            $params[':leave_type'] = $filters['leave_type'];
        }

        if ($filters['status_id'] !== '') {
            $where .= " AND l.status_id = :status_id";
            $params[':status_id'] = (int)$filters['status_id'];
        }

        // ---- Count total matching rows
        $countSql = "SELECT COUNT(*) AS total
                     FROM tbl_leave_applications l
                     INNER JOIN tbl_employees e ON l.employee_id = e.id
                     INNER JOIN tbl_leave_types t ON l.leave_type_id = t.id
                     WHERE $where";

        $stmt = $this->db->prepare($countSql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        $total = (int) ($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

        // ---- Fetch data with limit and offset
        $sql = "SELECT l.uuid, e.full_name AS employee_name, t.name AS leave_type,
                       l.start_date, l.end_date, l.reason, l.status_id, l.created_at
                FROM tbl_leave_applications l
                INNER JOIN tbl_employees e ON l.employee_id = e.id
                INNER JOIN tbl_leave_types t ON l.leave_type_id = t.id
                WHERE $where
                ORDER BY l.start_date DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);

        // Bind filters
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }

        // Bind pagination
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            "total" => $total,
            "rows"  => $data
        ];
    }

    /**
     * Get all leave types
     */
    public function getLeaveTypes(): array
    {
        $result = $this->db->query("SELECT DISTINCT name FROM tbl_leave_types ORDER BY name ASC");
        $types = [];
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $types[] = $row['name'];
        }
        return $types;
    }

    public function approveLeave(string $uuid): bool
    {
        $sql = "UPDATE tbl_leave_applications SET status_id = 2 WHERE uuid = :uuid";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':uuid', $uuid);
        return $stmt->execute();
    }
}
