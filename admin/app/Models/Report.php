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

        public function summary(string $from, string $to, ?string $department = null): array
    {
        $sql = "
            SELECT
                e.id,
                e.full_name,
                e.department,

                COUNT(DISTINCT d.work_date) AS total_days,

                SUM(
                    CASE
                        WHEN ar.check_type_id = 1 THEN 1
                        ELSE 0
                    END
                ) AS present_days,

                SUM(
                    CASE
                        WHEN ar.check_type_id = 1 AND ar.check_time > '08:00:00' THEN 1
                        ELSE 0
                    END
                ) AS late_days,

                COUNT(
                    CASE
                        WHEN lr.id IS NOT NULL THEN 1
                        ELSE NULL
                    END
                ) AS leave_days

            FROM tbl_employees e

            JOIN (
                SELECT DATE_ADD(:from1, INTERVAL seq DAY) AS work_date
                FROM (
                    SELECT 0 seq UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3
                    UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7
                    UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10 UNION ALL SELECT 11
                    UNION ALL SELECT 12 UNION ALL SELECT 13 UNION ALL SELECT 14 UNION ALL SELECT 15
                    UNION ALL SELECT 16 UNION ALL SELECT 17 UNION ALL SELECT 18 UNION ALL SELECT 19
                    UNION ALL SELECT 20 UNION ALL SELECT 21 UNION ALL SELECT 22 UNION ALL SELECT 23
                    UNION ALL SELECT 24 UNION ALL SELECT 25 UNION ALL SELECT 26 UNION ALL SELECT 27
                    UNION ALL SELECT 28 UNION ALL SELECT 29 UNION ALL SELECT 30
                ) seqs
                WHERE DATE_ADD(:from2, INTERVAL seq DAY) <= :to
            ) d

            LEFT JOIN tbl_attendance_records ar
                ON ar.employee_id = e.id
                AND ar.date = d.work_date

            LEFT JOIN tbl_leave_applications lr
                ON lr.employee_id = e.id
                AND d.work_date BETWEEN lr.start_date AND lr.end_date
                AND lr.status_id = 1 AND lr.deleted_at IS NULL

            WHERE e.status_id = 1
        ";

        if ($department) {
            $sql .= " AND e.department = :department";
        }

        $sql .= "
            GROUP BY e.id, e.full_name, e.department
            ORDER BY e.full_name
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':from1', $from);
        $stmt->bindValue(':from2', $from);
        $stmt->bindValue(':to', $to);

        if ($department) {
            $stmt->bindValue(':department', $department);
        }

        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$r) {
            $r['present_days']  = (int)$r['present_days'];
            $r['late_days']     = (int)$r['late_days'];
            $r['total_days']    = (int)$r['total_days'];
            $r['leave_days']    = (int)$r['leave_days'];
            $r['absent_days']   = max(0, $r['total_days'] - $r['present_days'] - $r['leave_days']);
            $r['attendance_percent'] = $r['total_days'] > 0
                ? round(($r['present_days'] / $r['total_days']) * 100, 2)
                : 0;
        }

        return $rows;
    }





}