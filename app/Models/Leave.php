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

  public function approveLeave(string $uuid, int $statusId): bool
{
    $sql = "UPDATE tbl_leave_applications
            SET status_id = :status_id
            WHERE uuid = :uuid";

    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':status_id', $statusId, PDO::PARAM_INT);
    $stmt->bindValue(':uuid', $uuid);

    return $stmt->execute();
}


    public function rejectLeave(string $uuid, string $remark): bool
    {
        $sql = "UPDATE tbl_leave_applications
                SET status_id = 2, remark = :remark
                WHERE uuid = :uuid";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':remark', $remark, PDO::PARAM_STR);
        $stmt->bindValue(':uuid', $uuid);

        return $stmt->execute();
    }




    public function create(int $employee_id, int $leave_type_id, string $start_date, string $end_date, string $reason): array
    {
        try {
            // 1. Check if leave_type_id exists
            $stmt = $this->db->prepare("SELECT id FROM tbl_leave_types WHERE id = :id");
            $stmt->bindValue(':id', $leave_type_id, \PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                return ['success' => false, 'error' => 'Invalid leave_type_id'];
            }

            // 2. Generate UUID
            $uuid = bin2hex(random_bytes(16));

            // 3. Insert leave application
            $stmt = $this->db->prepare("
                INSERT INTO tbl_leave_applications
                (uuid, employee_id, leave_type_id, start_date, end_date, reason, created_at)
                VALUES (:uuid, :employee_id, :leave_type_id, :start_date, :end_date, :reason, NOW())
            ");

            $stmt->bindValue(':uuid', $uuid);
            $stmt->bindValue(':employee_id', $employee_id, \PDO::PARAM_INT);
            $stmt->bindValue(':leave_type_id', $leave_type_id, \PDO::PARAM_INT);
            $stmt->bindValue(':start_date', $start_date);
            $stmt->bindValue(':end_date', $end_date);
            $stmt->bindValue(':reason', $reason);

            $stmt->execute();

            return [
                'success' => true,
                'data' => [
                    'uuid' => $uuid,
                    'id' => (int)$this->db->lastInsertId()
                ]
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
