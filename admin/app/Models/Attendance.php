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
        $offset = ($page - 1) * $perPage;
        $statusId = $filters['status_id'] ?? null;

        $records = $this->getList($perPage, $offset, $statusId);
        $total = $this->countAll($statusId);

        return [
            'records' => $records,
            'total' => $total
        ];
    }

    /**
     * Get a list of attendance records with optional status filter
     */
    public function getList(int $limit, int $offset, ?int $statusId): array
    {
        $sql = "SELECT * FROM tbl_attendance_records WHERE 1";

        if ($statusId !== null) {
            $sql .= " AND status_id = :status_id";
        }

        $sql .= " ORDER BY check_time DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);

        if ($statusId !== null) {
            $stmt->bindValue(':status_id', $statusId, PDO::PARAM_INT);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
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
     * Record a check-in for an employee
     */
    // public function checkIn(int $employeeId): bool
    // {
    //     try {
    //         $sql = "INSERT INTO tbl_attendance_records (uuid, employee_id, date, check_time, check_type_id, status_id, created_at)
    //                 VALUES (UUID(), :employee_id, CURDATE(), CURTIME(), 1, 1, NOW())";
    //         $stmt = $this->db->prepare($sql);
    //         $stmt->bindValue(':employee_id', $employeeId, PDO::PARAM_INT);
    //         return $stmt->execute();
    //     } catch (\Exception $e) {
    //         error_log("Attendance checkIn error: " . $e->getMessage());
    //         return false;
    //     }
    // }

    /**
     * Record a check-out for an employee
     */
    // public function checkOut(int $employeeId): bool
    // {
    //     try {
    //         $sql = "INSERT INTO tbl_attendance_records (uuid, employee_id, date, check_time, check_type_id, status_id, created_at)
    //                 VALUES (UUID(), :employee_id, CURDATE(), CURTIME(), 2, 1, NOW())";
    //         $stmt = $this->db->prepare($sql);
    //         $stmt->bindValue(':employee_id', $employeeId, PDO::PARAM_INT);
    //         return $stmt->execute();
    //     } catch (\Exception $e) {
    //         error_log("Attendance checkOut error: " . $e->getMessage());
    //         return false;
    //     }
    // }

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
            WHERE employee_id=? AND date=? AND check_type_id=? AND deleted_at IS NULL
        ");
        $stmt->execute([$employeeId, $date, $checkTypeId]);
        return (bool) $stmt->fetch();
    }

    public function insertScan(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO tbl_attendance_records
            (uuid, employee_id, date, check_time, check_type_id, status_id, created_at)
            VALUES (?,?,?,?,?,1,NOW())
        ");

        return $stmt->execute([
            $data['uuid'],
            $data['employee_id'],
            $data['date'],
            $data['check_time'],
            $data['check_type_id']
        ]);
    }

    public function getCheckType(int $id): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM tbl_check_types WHERE id=? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
