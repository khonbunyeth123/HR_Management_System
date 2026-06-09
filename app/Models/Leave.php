<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Enum\LeaveStatus;
use App\Support\Uuid;
use PDO;

/**
 * Model for leave applications.
 */
class Leave {
    private PDO $db;
    private string $table = 'tbl_leave_applications';
    private ?array $tableColumns = null;

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
    public function approveLeave(string $uuid, int $approvedBy): bool
    {
        $fields = [
            'status_id' => LeaveStatus::APPROVED->value,
            'approved_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->hasColumn('approved_by')) {
            $fields['approved_by'] = $approvedBy;
        }

        return $this->updateByUuid($uuid, $fields);
    }

    /**
     * Reject a leave application.
     */
    public function rejectLeave(string $uuid, int $rejectedBy, string $remark): bool
    {
        $fields = [
            'status_id' => LeaveStatus::REJECTED->value,
            'remark' => $remark,
            'rejected_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->hasColumn('rejected_by')) {
            $fields['rejected_by'] = $rejectedBy;
        }

        return $this->updateByUuid($uuid, $fields);
    }

    public function reopenLeave(string $uuid, int $actorId): bool
    {
        $fields = [
            'status_id' => LeaveStatus::PENDING->value,
        ];

        if ($this->hasColumn('approved_at')) {
            $fields['approved_at'] = null;
        }
        if ($this->hasColumn('approved_by')) {
            $fields['approved_by'] = null;
        }
        if ($this->hasColumn('rejected_at')) {
            $fields['rejected_at'] = null;
        }
        if ($this->hasColumn('rejected_by')) {
            $fields['rejected_by'] = null;
        }

        return $this->updateByUuid($uuid, $fields);
    }

    public function cancelApproval(string $uuid, int $actorId): bool
    {
        $fields = [
            'status_id' => LeaveStatus::PENDING->value,
        ];

        if ($this->hasColumn('approved_at')) {
            $fields['approved_at'] = null;
        }
        if ($this->hasColumn('approved_by')) {
            $fields['approved_by'] = null;
        }

        return $this->updateByUuid($uuid, $fields);
    }

    /**
     * Create a new leave application.
     */
    public function create(int $employee_id, int $leave_type_id, string $start_date, string $end_date, string $reason): array
    {
        try {
            if (!$this->employeeExists($employee_id)) {
                return ['success' => false, 'error' => 'Employee not found'];
            }

            if (!$this->leaveTypeExists($leave_type_id)) {
                return ['success' => false, 'error' => 'Leave type not found'];
            }

            $uuid = Uuid::v4();
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
        $result = $this->db->query(
            "SELECT DISTINCT name FROM tbl_leave_types WHERE deleted_at IS NULL ORDER BY name ASC"
        );
        $types = [];
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $types[] = $row['name'];
        }
        return $types;
    }

    public function getLeaveTypeIdByName(string $name): ?int
    {
        $stmt = $this->db->prepare(
            "SELECT id FROM tbl_leave_types
             WHERE LOWER(TRIM(name)) = LOWER(TRIM(:name))
               AND deleted_at IS NULL
             LIMIT 1"
        );
        $stmt->bindValue(':name', trim($name));
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ? (int)$row['id'] : null;
    }

    private function updateByUuid(string $uuid, array $fields): bool
    {
        $set = [];
        $params = [':uuid' => $uuid];

        foreach ($fields as $column => $value) {
            if (!$this->hasColumn($column)) {
                continue;
            }

            $set[] = $column . ' = :' . $column;
            $params[':' . $column] = $value;
        }

        if (empty($set)) {
            return false;
        }

        $sql = 'UPDATE ' . $this->table . ' SET ' . implode(', ', $set) . ' WHERE uuid = :uuid';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    private function hasColumn(string $column): bool
    {
        if ($this->tableColumns === null) {
            $this->tableColumns = [];
            $stmt = $this->db->query('SHOW COLUMNS FROM ' . $this->table);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
                if (!empty($col['Field'])) {
                    $this->tableColumns[$col['Field']] = true;
                }
            }
        }

        return isset($this->tableColumns[$column]);
    }

    private function employeeExists(int $employeeId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM tbl_employees WHERE id = :id AND deleted_at IS NULL LIMIT 1'
        );
        $stmt->execute([':id' => $employeeId]);
        return (bool) $stmt->fetchColumn();
    }

    private function leaveTypeExists(int $leaveTypeId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM tbl_leave_types WHERE id = :id AND deleted_at IS NULL LIMIT 1'
        );
        $stmt->execute([':id' => $leaveTypeId]);
        return (bool) $stmt->fetchColumn();
    }
}
