<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Attendance
{
    private PDO $db;

    public function __construct()
    {
        try {
            $this->db = Database::getInstance()->getConnection();

            if (!$this->db) {
                throw new \Exception('Failed to establish database connection');
            }
        } catch (\Exception $e) {
            error_log("Attendance Model - Database Connection Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Return paginated attendance records
     */
    public function getPaginated(int $page = 1, int $perPage = 18, array $filters = []): array
    {
        $offset   = ($page - 1) * $perPage;
        $statusId = $filters['status_id'] ?? null;

        $records = $this->getList($perPage, $offset, $statusId);
        $total   = $this->countAll($statusId);

        return [
            'records' => $records,
            'total'   => $total
        ];
    }

    /**
     * Get a list of attendance records with optional status filter
     */
    public function getList(int $limit, int $offset, ?int $statusId): array
    {
        $sql = "
            SELECT 
                a.*,
                CAST(e.id AS CHAR) AS emp_code,
                e.full_name,
                ct.name AS check_type_name
            FROM tbl_attendance_records a
            LEFT JOIN tbl_employees e 
                ON a.employee_id = e.id
            LEFT JOIN tbl_check_types ct
                ON a.check_type_id = ct.id
            WHERE 1
        ";

        if ($statusId !== null) {
            $sql .= " AND a.status_id = :status_id";
        }

        $sql .= " ORDER BY a.check_time DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);

        if ($statusId !== null) {
            $stmt->bindValue(':status_id', $statusId, PDO::PARAM_INT);
        }

        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Count all records for optional status filter
     */
    public function countAll(?int $statusId): int
    {
        $sql = "SELECT COUNT(*) FROM tbl_attendance_records WHERE 1";

        if ($statusId !== null) {
            $sql .= " AND status_id = :status_id";
        }

        $stmt = $this->db->prepare($sql);

        if ($statusId !== null) {
            $stmt->bindValue(':status_id', $statusId, PDO::PARAM_INT);
        }

        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }

    /**
     * Get today's attendance for an employee
     */
    public function getTodayAttendance(int $employeeId): ?array
    {
        $sql = "SELECT * FROM tbl_attendance_records 
                WHERE employee_id = :employee_id 
                  AND date = CURDATE()";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':employee_id', $employeeId, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    public function getTodayScanCount(int $employeeId, string $date): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) 
            FROM tbl_attendance_records
            WHERE employee_id = ? 
            AND date = ?
            AND deleted_at IS NULL
        ");
        $stmt->execute([$employeeId, $date]);
        return (int) $stmt->fetchColumn();
    }

    public function existsScan(int $employeeId, string $date, int $checkTypeId): bool
    {
        $stmt = $this->db->prepare("
            SELECT id FROM tbl_attendance_records
            WHERE employee_id = ? AND date = ? AND check_type_id = ? AND deleted_at IS NULL
        ");
        $stmt->execute([$employeeId, $date, $checkTypeId]);
        return (bool) $stmt->fetch();
    }

    public function insertScan(array $data): bool
    {
        try {
            $sql = "
                INSERT INTO tbl_attendance_records
                (uuid, employee_id, date, check_time, check_type_id, status_id, created_at)
                VALUES (:uuid, :employee_id, :date, :check_time, :check_type_id, 1, NOW())
            ";

            $stmt = $this->db->prepare($sql);

            $success = $stmt->execute([
                ':uuid'          => $data['uuid'],
                ':employee_id'   => $data['employee_id'],
                ':date'          => $data['date'],
                ':check_time'    => $data['check_time'],
                ':check_type_id' => $data['check_type_id'],
            ]);

            if (!$success) {
                error_log(json_encode($stmt->errorInfo()));
            }

            return $success;

        } catch (\Throwable $e) {
            error_log("Insert Scan Error: " . $e->getMessage());
            return false;
        }
    }

    public function getCheckType(int $id): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM tbl_check_types WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findActiveEmployeeByUuid(string $uuid): ?array
    {
        $stmt = $this->db->prepare("
            SELECT id, uuid, full_name
            FROM tbl_employees
            WHERE uuid = :uuid
              AND status_id = 1
              AND deleted_at IS NULL
            LIMIT 1
        ");
        $stmt->bindValue(':uuid', $uuid);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findActiveEmployeeById(int $employeeId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT id, uuid, full_name
            FROM tbl_employees
            WHERE id = :employee_id
              AND status_id = 1
              AND deleted_at IS NULL
            LIMIT 1
        ");
        $stmt->bindValue(':employee_id', $employeeId, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getActiveEmployees(): array
    {
        $stmt = $this->db->query("
            SELECT id, full_name 
            FROM tbl_employees 
            WHERE status_id = 1 AND deleted_at IS NULL 
            ORDER BY full_name
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSlotByHour(): array
    {
        $hour = (int) date('H');
        if ($hour >= 7  && $hour < 12) return ['slot' => 1, 'label' => 'Morning Check-in',   'check_type_id' => 1];
        if ($hour >= 12 && $hour < 13) return ['slot' => 2, 'label' => 'Morning Check-out',  'check_type_id' => 2];
        if ($hour >= 14 && $hour < 18) return ['slot' => 3, 'label' => 'Afternoon Check-in', 'check_type_id' => 3];
        if ($hour >= 18 && $hour <= 21) return ['slot' => 4, 'label' => 'Afternoon Check-out','check_type_id' => 4];
        return ['slot' => 0, 'label' => 'Outside office hours', 'check_type_id' => 0];
    }

    public function getByEmployeeId(int $employeeId, int $limit, int $offset): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                a.uuid,
                a.date,
                a.check_time,
                ct.name AS check_type_name,
                a.status_id,
                a.created_at
            FROM tbl_attendance_records a
            LEFT JOIN tbl_check_types ct ON a.check_type_id = ct.id
            WHERE a.employee_id = :employee_id
            AND a.deleted_at IS NULL
            ORDER BY a.check_time DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':employee_id', $employeeId, PDO::PARAM_INT);
        $stmt->bindValue(':limit',       $limit,      PDO::PARAM_INT);
        $stmt->bindValue(':offset',      $offset,     PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countByEmployeeId(int $employeeId): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM tbl_attendance_records
            WHERE employee_id = :employee_id
            AND deleted_at IS NULL
        ");
        $stmt->bindValue(':employee_id', $employeeId, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }
}
