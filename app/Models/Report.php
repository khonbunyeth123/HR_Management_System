<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Report
{
    protected PDO $pdo;
    private ?bool $hasScanDatetime = null;
    private ?bool $hasStatus = null;

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
                MAX(CASE WHEN a.check_type_id = 1 THEN {$this->scanDatetimeExpr('a')} ELSE NULL END) as check_in_1,
                MAX(CASE WHEN a.check_type_id = 1 THEN {$this->statusExpr('a')} ELSE NULL END) as check_in_1_status,
                MAX(CASE WHEN a.check_type_id = 2 THEN {$this->scanDatetimeExpr('a')} ELSE NULL END) as check_out_1,
                MAX(CASE WHEN a.check_type_id = 2 THEN {$this->statusExpr('a')} ELSE NULL END) as check_out_1_status,
                MAX(CASE WHEN a.check_type_id = 3 THEN {$this->scanDatetimeExpr('a')} ELSE NULL END) as check_in_2,
                MAX(CASE WHEN a.check_type_id = 3 THEN {$this->statusExpr('a')} ELSE NULL END) as check_in_2_status,
                MAX(CASE WHEN a.check_type_id = 4 THEN {$this->scanDatetimeExpr('a')} ELSE NULL END) as check_out_2,
                MAX(CASE WHEN a.check_type_id = 4 THEN {$this->statusExpr('a')} ELSE NULL END) as check_out_2_status
            FROM tbl_employees e
            LEFT JOIN tbl_attendance_records a
                ON e.id = a.employee_id
                AND DATE(a.date) = :date
                AND a.deleted_at IS NULL
            LEFT JOIN tbl_check_types ct
                ON ct.id = a.check_type_id
            WHERE e.status_id = 1
            GROUP BY e.id, e.full_name, e.username, e.position, e.department
            ORDER BY e.full_name ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['date' => $date ?: date('Y-m-d')]);

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

                SUM(CASE WHEN ar.check_type_id = 1 THEN 1 ELSE 0 END) AS present_days,
                SUM(CASE WHEN ar.check_type_id IN (1,3) AND TIME({$this->scanDatetimeExpr('ar')}) > CASE WHEN ar.check_type_id = 1 THEN '08:00:00' ELSE '13:00:00' END THEN 1 ELSE 0 END) AS late_days,

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
                    SELECT (a.i + b.i * 10) AS seq
                    FROM (SELECT 0 i UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) a
                    CROSS JOIN (SELECT 0 i UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) b
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

    public function fetchDetailedAttendance(string $from,string $to,?string $department = null,?string $search = null,?string $status = null): array 
    {
                $sql = "
                    SELECT
                        e.id            AS employee_id,
                        e.full_name,
                        e.department,
                        a.date,
                        DAYNAME(a.date) AS day_name,
                        ct.name         AS check_type,
                        ct.standard_time,
                        {$this->scanDatetimeExpr('a')} AS scan_datetime,
                        {$this->statusExpr('a')} AS status,
                        TIME({$this->scanDatetimeExpr('a')}) AS check_time,
                        TIMESTAMPDIFF(
                            MINUTE,
                            ct.standard_time,
                            TIME({$this->scanDatetimeExpr('a')})
                        ) AS diff_minutes
                    FROM tbl_attendance_records a
                    JOIN tbl_employees e
                        ON e.id = a.employee_id
                    JOIN tbl_check_types ct
                        ON ct.id = a.check_type_id
                    WHERE a.deleted_at IS NULL
                    AND e.status_id = 1
                    AND a.date BETWEEN :from AND :to
                ";

        $params = [':from' => $from, ':to' => $to];

        if ($department) {
            $sql .= " AND e.department = :department";
            $params[':department'] = $department;
        }

        if ($search) {
            $sql .= " AND (e.full_name LIKE :search OR CAST(e.id AS CHAR) LIKE :search2)";
            $params[':search']  = "%$search%";
            $params[':search2'] = "%$search%";
        }

        $sql .= " ORDER BY a.date DESC, e.full_name ASC, ct.id ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Apply status filter after computing status
        $result = [];
        foreach ($rows as $row) {
            $diff   = (int)$row['diff_minutes'];
            $checkType = (string)($row['check_type'] ?? '');
            $status_val = (string)($row['status'] ?: $this->resolvePunchStatus($checkType, $row['check_time'], $row['standard_time']));

            if ($status && $status !== $status_val) continue;

            $result[] = [
                'employee_id'   => $row['employee_id'],
                'name'          => $row['full_name'],
                'department'    => $row['department'],
                'date'          => $row['date'],
                'day'           => $row['day_name'],
                'check_type'    => $row['check_type'],
                'standard_time' => date('h:i A', strtotime($row['standard_time'])),
                'actual_time'   => date('h:i A', strtotime($row['scan_datetime'])),
                'diff'          => $diff,
                'status'        => $status_val,
                'late_minutes'  => in_array($status_val, ['Late', 'Overtime'], true) && $diff > 0 ? $diff : 0,
                'early_leave_minutes' => $status_val === 'Early Leave' && $diff < 0 ? abs($diff) : 0,
                'overtime_minutes' => $status_val === 'Overtime' && $diff > 0 ? $diff : 0,
            ];
        }

        return $result;
    }

    public function fetchAttendanceDailyRows(string $from, string $to, ?string $department = null, ?string $search = null): array
    {
        $sql = "
            SELECT
                e.id AS employee_id,
                e.full_name,
                e.department,
                a.date,
                DAYNAME(a.date) AS day_name,
                ct.name AS check_type,
                ct.standard_time,
                {$this->scanDatetimeExpr('a')} AS scan_datetime,
                {$this->statusExpr('a')} AS status
            FROM tbl_employees e
            LEFT JOIN tbl_attendance_records a
                ON a.employee_id = e.id
               AND a.deleted_at IS NULL
               AND a.date BETWEEN :from AND :to
            LEFT JOIN tbl_check_types ct
                ON ct.id = a.check_type_id
            WHERE e.status_id = 1
        ";

        $params = [':from' => $from, ':to' => $to];

        if ($department) {
            $sql .= " AND e.department = :department";
            $params[':department'] = $department;
        }

        if ($search) {
            $sql .= " AND (e.full_name LIKE :search OR CAST(e.id AS CHAR) LIKE :search2)";
            $params[':search'] = "%$search%";
            $params[':search2'] = "%$search%";
        }

        $sql .= " ORDER BY e.full_name ASC, a.date ASC, ct.id ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchApprovedLeaves(string $from, string $to, ?array $employeeIds = null): array
    {
        $sql = "
            SELECT employee_id, start_date, end_date, lt.name AS leave_type
            FROM tbl_leave_applications la
            INNER JOIN tbl_leave_types lt ON lt.id = la.leave_type_id
            WHERE la.status_id = 1
              AND la.deleted_at IS NULL
              AND la.start_date <= :to
              AND la.end_date >= :from
        ";

        $params = [':from' => $from, ':to' => $to];
        if (!empty($employeeIds)) {
            $placeholders = implode(',', array_fill(0, count($employeeIds), '?'));
            $sql .= " AND la.employee_id IN ($placeholders)";
        }

        $stmt = $this->pdo->prepare($sql);
        $i = 1;
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        if (!empty($employeeIds)) {
            foreach ($employeeIds as $id) {
                $stmt->bindValue($i++, (int) $id, PDO::PARAM_INT);
            }
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchPublicHolidays(string $from, string $to): array
    {
        $stmt = $this->pdo->prepare("
            SELECT DISTINCT DATE(start_at) AS holiday_date, title
            FROM tbl_calendar_events
            WHERE deleted_at IS NULL
              AND event_type = 'holiday'
              AND status = 'approved'
              AND DATE(start_at) <= :to
              AND DATE(end_at) >= :from
            ORDER BY holiday_date ASC, title ASC
        ");
        $stmt->execute([':from' => $from, ':to' => $to]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function resolvePunchStatus(string $checkType, ?string $actualTime, ?string $standardTime): string
    {
        if (!$actualTime || !$standardTime) {
            return 'Recorded';
        }

        $isCheckIn = in_array($checkType, ['Check-in 1', 'Check-in 2'], true);

        if (!$isCheckIn) {
            return 'On Time';
        }

        return strtotime($actualTime) > strtotime($standardTime) ? 'Late' : 'On Time';
    }

    public function fetchTopEmployees(string $from, string $to): array
    {
        $sql = "
            SELECT
                e.id,
                e.full_name,
                e.department,

                -- Count distinct days employee checked in
                COUNT(DISTINCT CASE WHEN a.check_type_id = 1 THEN a.date END) AS present_days,
                COUNT(DISTINCT CASE WHEN a.check_type_id IN (1,3) AND {$this->statusExpr('a')} = 'Late' THEN a.date END) AS late_days,

                -- Total working days in range
                DATEDIFF(:to, :from) + 1 AS total_days

            FROM tbl_employees e
            LEFT JOIN tbl_attendance_records a
                ON a.employee_id = e.id
                AND a.date BETWEEN :from2 AND :to2
                AND a.deleted_at IS NULL
            WHERE e.status_id = 1
            GROUP BY e.id, e.full_name, e.department
            ORDER BY present_days DESC, late_days ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':from',  $from);
        $stmt->bindValue(':to',    $to);
        $stmt->bindValue(':from2', $from);
        $stmt->bindValue(':to2',   $to);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$r) {
            $total   = (int)$r['total_days'];
            $present = (int)$r['present_days'];
            $late    = (int)$r['late_days'];
            $absent  = max(0, $total - $present);

            $r['present_days']       = $present;
            $r['late_days']          = $late;
            $r['absent_days']        = $absent;
            $r['total_days']         = $total;
            $r['attendance_percent'] = $total > 0
                ? round(($present / $total) * 100, 1)
                : 0;
        }

        return $rows;
    }

    private function scanDatetimeExpr(string $alias = 'tbl_attendance_records'): string
    {
        if ($this->hasScanDatetimeColumn()) {
            return "{$alias}.scan_datetime";
        }

        return "CONCAT({$alias}.date, ' ', {$alias}.check_time)";
    }

    private function hasScanDatetimeColumn(): bool
    {
        if ($this->hasScanDatetime !== null) {
            return $this->hasScanDatetime;
        }

        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*)
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'tbl_attendance_records'
               AND COLUMN_NAME = 'scan_datetime'"
        );
        $stmt->execute();
        $this->hasScanDatetime = ((int) $stmt->fetchColumn()) > 0;
        return $this->hasScanDatetime;
    }

    private function statusExpr(string $alias = 'tbl_attendance_records'): string
    {
        if ($this->hasStatusColumn()) {
            return "{$alias}.status";
        }

        $scanExpr = $this->scanDatetimeExpr($alias);

        return "CASE
                    WHEN {$alias}.check_type_id IN (1, 3)
                         AND {$scanExpr} > CASE WHEN {$alias}.check_type_id = 1
                             THEN CONCAT({$alias}.date, ' 08:00:00')
                             ELSE CONCAT({$alias}.date, ' 13:00:00')
                         END
                        THEN 'Late'
                    WHEN {$alias}.check_type_id = 2
                         AND {$scanExpr} < CONCAT({$alias}.date, ' 12:00:00')
                        THEN 'Early Leave'
                    WHEN {$alias}.check_type_id = 4
                         AND {$scanExpr} > CONCAT({$alias}.date, ' 17:00:00')
                        THEN 'Overtime'
                    WHEN {$alias}.check_type_id IN (2, 4)
                         AND {$scanExpr} < CASE WHEN {$alias}.check_type_id = 2
                             THEN CONCAT({$alias}.date, ' 12:00:00')
                             ELSE CONCAT({$alias}.date, ' 17:00:00')
                         END
                        THEN 'Early Leave'
                    ELSE 'On Time'
                END";
    }

    private function hasStatusColumn(): bool
    {
        if ($this->hasStatus !== null) {
            return $this->hasStatus;
        }

        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*)
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'tbl_attendance_records'
               AND COLUMN_NAME = 'status'"
        );
        $stmt->execute();
        $this->hasStatus = ((int) $stmt->fetchColumn()) > 0;
        return $this->hasStatus;
    }

    


}
