<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Enum\LeaveStatus;
use PDO;

/**
 * Model for attendance records.
 */
class Attendance
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getList(int $limit, int $offset, ?int $statusId = null, ?string $date = null, ?string $search = null, ?string $checkType = null): array
    {
        $params = [];
        $where = "a.deleted_at IS NULL";

        if ($statusId !== null && $statusId !== 0) {
            $where .= " AND a.status_id = ?";
            $params[] = $statusId;
        }

        if ($date !== null && $date !== '') {
            $where .= " AND a.date = ?";
            $params[] = $date;
        }

        if ($search !== null && $search !== '') {
            $where .= " AND (e.full_name LIKE ? OR CAST(e.id AS CHAR) LIKE ?)";
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }

        if ($checkType !== null && $checkType !== '') {
            $where .= " AND ct.name = ?";
            $params[] = $checkType;
        }

        $sql = "SELECT a.uuid, a.employee_id, a.date, a.check_time, a.status_id, a.created_at,
                       CAST(e.id AS CHAR) AS emp_code, e.full_name, ct.name AS check_type_name, 'scan' as record_source
                FROM tbl_attendance_records a
                LEFT JOIN tbl_employees e ON a.employee_id = e.id
                LEFT JOIN tbl_check_types ct ON a.check_type_id = ct.id
                WHERE $where
                ORDER BY a.date DESC, a.check_time DESC
                LIMIT ? OFFSET ?";

        $stmt = $this->db->prepare($sql);
        
        $i = 1;
        foreach ($params as $val) {
            $stmt->bindValue($i++, $val);
        }
        
        $stmt->bindValue($i++, $limit, PDO::PARAM_INT);
        $stmt->bindValue($i++, $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countAll(?int $statusId = null, ?string $date = null, ?string $search = null, ?string $checkType = null): int
    {
        $params = [];
        $where = "a.deleted_at IS NULL";

        if ($statusId !== null && $statusId !== 0) {
            $where .= " AND a.status_id = ?";
            $params[] = $statusId;
        }

        if ($date !== null && $date !== '') {
            $where .= " AND a.date = ?";
            $params[] = $date;
        }

        if ($search !== null && $search !== '') {
            $where .= " AND (e.full_name LIKE ? OR CAST(e.id AS CHAR) LIKE ?)";
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }

        if ($checkType !== null && $checkType !== '') {
            $where .= " AND ct.name = ?";
            $params[] = $checkType;
        }

        $sql = "SELECT COUNT(*) FROM tbl_attendance_records a 
                LEFT JOIN tbl_employees e ON a.employee_id = e.id 
                LEFT JOIN tbl_check_types ct ON a.check_type_id = ct.id
                WHERE $where";
        
        $stmt = $this->db->prepare($sql);
        $i = 1;
        foreach ($params as $val) {
            $stmt->bindValue($i++, $val);
        }
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function getTodayScanCount(int $employeeId, string $date): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM tbl_attendance_records WHERE employee_id = ? AND date = ? AND deleted_at IS NULL");
        $stmt->execute([$employeeId, $date]);
        return (int)$stmt->fetchColumn();
    }

    public function existsScan(int $employeeId, string $date, int $checkTypeId): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM tbl_attendance_records WHERE employee_id = ? AND date = ? AND check_type_id = ? AND deleted_at IS NULL LIMIT 1");
        $stmt->execute([$employeeId, $date, $checkTypeId]);
        return (bool)$stmt->fetchColumn();
    }

    public function getCheckType(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM tbl_check_types WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getCheckTypeIdByName(string $name): ?int
    {
        $stmt = $this->db->prepare(
            "SELECT id FROM tbl_check_types WHERE LOWER(TRIM(name)) = LOWER(TRIM(:name)) AND deleted_at IS NULL LIMIT 1"
        );
        $stmt->bindValue(':name', trim($name));
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int) $row['id'] : null;
    }

    public function insertScan(array $data): bool
    {
        $sql = "INSERT INTO tbl_attendance_records (uuid, employee_id, date, check_time, check_type_id, status_id, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['uuid'],
            $data['employee_id'],
            $data['date'],
            $data['check_time'],
            $data['check_type_id'],
            $data['status_id'] ?? 1
        ]);
    }

    public function getActiveEmployees(): array
    {
        $stmt = $this->db->query("SELECT id, full_name FROM tbl_employees WHERE deleted_at IS NULL ORDER BY full_name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSlotByHour(): array
    {
        $hour = (int)date('H');
        
        if ($hour >= 7 && $hour < 12) {
            return ['slot' => 1, 'label' => 'Check-in 1 (Morning)'];
        } elseif ($hour >= 12 && $hour < 13) {
            return ['slot' => 2, 'label' => 'Check-out 1 (Lunch)'];
        } elseif ($hour >= 14 && $hour < 18) {
            return ['slot' => 3, 'label' => 'Check-in 2 (Afternoon)'];
        } elseif ($hour >= 18 && $hour < 21) {
            return ['slot' => 4, 'label' => 'Check-out 2 (Evening)'];
        }
        
        return ['slot' => 0, 'label' => 'Out of Office Hours'];
    }

    public function getByEmployeeId(int $employeeId, int $limit, int $offset): array
    {
        $sql = "SELECT a.uuid, a.date, a.check_time, ct.name AS check_type_name
                FROM tbl_attendance_records a
                LEFT JOIN tbl_check_types ct ON a.check_type_id = ct.id
                WHERE a.employee_id = ? AND a.deleted_at IS NULL
                ORDER BY a.date DESC, a.check_time DESC
                LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, $employeeId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->bindValue(3, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countByEmployeeId(int $employeeId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM tbl_attendance_records WHERE employee_id = ? AND deleted_at IS NULL");
        $stmt->execute([$employeeId]);
        return (int)$stmt->fetchColumn();
    }
}
