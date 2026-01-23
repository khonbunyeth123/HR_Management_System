<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Report
{
    protected PDO $pdo;

    public function __construct()
    {
        // Get the PDO connection
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Fetch daily attendance with pivoted check times
     */
    public function fetchDailyAttendance(?string $date = null): array
    {
        $sql = "
            SELECT
                e.id as employee_id,
                e.full_name,
                e.username,
                e.position,
                e.department,
                MAX(CASE WHEN a.check_type_id = 1 THEN a.check_time ELSE NULL END) as check_in_1,
                MAX(CASE WHEN a.check_type_id = 2 THEN a.check_time ELSE NULL END) as check_out_1,
                NULL as check_in_2,
                NULL as check_out_2,
                'Present' as status
            FROM tbl_employees e
            INNER JOIN tbl_attendance_records a
                ON e.id = a.employee_id
            WHERE e.status_id = 1
        ";

        $params = [];

        if ($date) {
            $sql .= " AND DATE(a.date) = :date";
            $params['date'] = $date;
        }

        $sql .= " GROUP BY e.id, e.full_name, e.username, e.position, e.department
                  ORDER BY e.full_name ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}