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

    /**
     * Get attendance records with filters and pagination.
     */
    public function getPaginated(int $page = 1, int $perPage = 18, array $filters = []): array
    {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $where = "a.deleted_at IS NULL";

        if (isset($filters['status_id'])) {
            $where .= " AND a.status_id = :status_id";
            $params[':status_id'] = $filters['status_id'];
        }

        if (!empty($filters['date'])) {
            $where .= " AND a.date = :date";
            $params[':date'] = $filters['date'];
        }

        if (!empty($filters['search'])) {
            $where .= " AND (e.full_name LIKE :search OR CAST(e.id AS CHAR) LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        // Count Scans only (Union All removed as requested)
        $countSql = "SELECT COUNT(*) FROM tbl_attendance_records a 
                     LEFT JOIN tbl_employees e ON a.employee_id = e.id 
                     WHERE $where";
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $total = (int)$stmt->fetchColumn();

        // Fetch Scans
        $sql = "SELECT a.uuid, a.employee_id, a.date, a.check_time, a.status_id, a.created_at,
                       CAST(e.id AS CHAR) AS emp_code, e.full_name, ct.name AS check_type_name, 'scan' as record_source
                FROM tbl_attendance_records a
                LEFT JOIN tbl_employees e ON a.employee_id = e.id
                LEFT JOIN tbl_check_types ct ON a.check_type_id = ct.id
                WHERE $where
                ORDER BY a.date DESC, a.check_time DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ['records' => $records, 'total' => $total];
    }
    
    // ... rest of the methods (simplified for the refactor)
}
