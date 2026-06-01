<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Enum\LeaveStatus;
use PDO;

/**
 * Model for leave applications.
 */
class Leave {
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all leave applications with filters and pagination.
     */
    public function getAll(array $filters, int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;
        $where = "1=1";
        $params = [];

        if (!empty($filters['employee_name'])) {
            $where .= " AND e.full_name LIKE :employee_name";
            $params[':employee_name'] = "%" . $filters['employee_name'] . "%";
        }

        if (!empty($filters['leave_type'])) {
            $where .= " AND t.name = :leave_type";
            $params[':leave_type'] = $filters['leave_type'];
        }

        if (isset($filters['status_id']) && $filters['status_id'] !== '') {
            $where .= " AND l.status_id = :status_id";
            $params[':status_id'] = (int)$filters['status_id'];
        }

        if (!empty($filters['uuid'])) {
            $where .= " AND l.uuid = :uuid";
            $params[':uuid'] = $filters['uuid'];
        }

        $countSql = "SELECT COUNT(*) AS total
                     FROM tbl_leave_applications l
                     INNER JOIN tbl_employees e ON l.employee_id = e.id
                     INNER JOIN tbl_leave_types t ON l.leave_type_id = t.id
                     WHERE $where";

        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $total = (int) ($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

        $sql = "SELECT l.id, l.uuid, l.employee_id, e.full_name AS employee_name, t.name AS leave_type,
                       l.start_date, l.end_date, l.reason, l.status_id, l.created_at, e.department
                FROM tbl_leave_applications l
                INNER JOIN tbl_employees e ON l.employee_id = e.id
                INNER JOIN tbl_leave_types t ON l.leave_type_id = t.id
                WHERE $where
                ORDER BY l.start_date DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ["total" => $total, "rows" => $data];
    }

    /**
     * Approve a leave application.
     */
    public function approveLeave(string $uuid, int $statusId): bool
    {
        $sql = "UPDATE tbl_leave_applications SET status_id = :status_id, approved_at = NOW() WHERE uuid = :uuid";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':status_id' => $statusId, ':uuid' => $uuid]);
    }

    /**
     * Reject a leave application.
     */
    public function rejectLeave(string $uuid, string $remark): bool
    {
        $sql = "UPDATE tbl_leave_applications SET status_id = :status_id, remark = :remark, rejected_at = NOW() WHERE uuid = :uuid";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':status_id' => LeaveStatus::REJECTED->value,
            ':remark' => $remark,
            ':uuid' => $uuid
        ]);
    }

    /**
     * Create a new leave application.
     */
    public function create(int $employee_id, int $leave_type_id, string $start_date, string $end_date, string $reason): array
    {
        try {
            $uuid = bin2hex(random_bytes(16));
            $stmt = $this->db->prepare("
                INSERT INTO tbl_leave_applications (uuid, employee_id, leave_type_id, start_date, end_date, reason, status_id, created_at)
                VALUES (:uuid, :employee_id, :leave_type_id, :start_date, :end_date, :reason, :status_id, NOW())
            ");

            $stmt->execute([
                ':uuid' => $uuid,
                ':employee_id' => $employee_id,
                ':leave_type_id' => $leave_type_id,
                ':start_date' => $start_date,
                ':end_date' => $end_date,
                ':reason' => $reason,
                ':status_id' => LeaveStatus::PENDING->value
            ]);

            return ['success' => true, 'data' => ['uuid' => $uuid, 'id' => (int)$this->db->lastInsertId()]];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get leave applications by employee ID.
     */
    public function getByEmployeeId(array $filters, int $limit, int $offset): array
    {
        $employeeId = (int)$filters['employee_id'];
        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM tbl_leave_applications WHERE employee_id = :employee_id");
        $countStmt->execute([':employee_id' => $employeeId]);
        $total = (int) $countStmt->fetchColumn();

        $stmt = $this->db->prepare("
            SELECT l.id, l.uuid, t.name AS leave_type, l.start_date, l.end_date, l.reason, l.status_id, l.created_at
            FROM tbl_leave_applications l
            INNER JOIN tbl_leave_types t ON l.leave_type_id = t.id
            WHERE l.employee_id = :employee_id
            ORDER BY l.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':employee_id', $employeeId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return ['total' => $total, 'rows' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    /**
     * Get all leave types.
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

    public function getLeaveTypeIdByName(string $name): ?int
    {
        $stmt = $this->db->prepare("SELECT id FROM tbl_leave_types WHERE name = :name LIMIT 1");
        $stmt->bindValue(':name', $name);
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ? (int)$row['id'] : null;
    }
}
