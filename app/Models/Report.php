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
            $i = 0;
            $placeholders = [];
            foreach ($employeeIds as $id) {
                $key = ":emp_id_$i";
                $placeholders[] = $key;
                $params[$key] = (int) $id;
                $i++;
            }
            $sql .= " AND la.employee_id IN (" . implode(',', $placeholders) . ")";
        }

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $k => $v) {
            if (is_int($v)) {
                $stmt->bindValue($k, $v, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($k, $v);
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

    private function scanDatetimeExpr(string $alias = 'tbl_attendance_records'): string
    {
            $fallback = "CONCAT({$alias}.date, ' ', {$alias}.check_time)";
        if ($this->hasScanDatetimeColumn()) {
            return "COALESCE({$alias}.scan_datetime, {$fallback})";
        }

        return $fallback;
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
        $scanExpr = $this->scanDatetimeExpr($alias);
        $calculatedStatus = "CASE
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

        if ($this->hasStatusColumn()) {
            return "COALESCE({$alias}.status, {$calculatedStatus})";
        }

        return $calculatedStatus;
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
