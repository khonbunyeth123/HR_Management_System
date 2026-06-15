<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use App\Core\Database;

class Payroll
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /* ================= SALARY CONFIGURATIONS ================= */

    public function getSalaryConfigByEmployeeId(int $employeeId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM tbl_salary_configurations WHERE employee_id = :employee_id LIMIT 1");
        $stmt->execute(['employee_id' => $employeeId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function updateSalaryConfig(int $employeeId, array $data): bool
    {
        $config = $this->getSalaryConfigByEmployeeId($employeeId);

        if ($config) {
            $sql = "UPDATE tbl_salary_configurations SET 
                    base_salary = :base_salary,
                    allowance_transport = :allowance_transport,
                    allowance_food = :allowance_food,
                    allowance_phone = :allowance_phone,
                    allowance_other = :allowance_other,
                    updated_at = NOW()
                    WHERE employee_id = :employee_id";
        } else {
            $sql = "INSERT INTO tbl_salary_configurations 
                    (employee_id, base_salary, allowance_transport, allowance_food, allowance_phone, allowance_other, created_at)
                    VALUES 
                    (:employee_id, :base_salary, :allowance_transport, :allowance_food, :allowance_phone, :allowance_other, NOW())";
        }

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'employee_id'         => $employeeId,
            'base_salary'         => $data['base_salary'] ?? 0,
            'allowance_transport' => $data['allowance_transport'] ?? 0,
            'allowance_food'      => $data['allowance_food'] ?? 0,
            'allowance_phone'     => $data['allowance_phone'] ?? 0,
            'allowance_other'     => $data['allowance_other'] ?? 0
        ]);
    }

    /* ================= PAYROLL PERIODS ================= */

    public function getPeriod(int $month, int $year): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM tbl_payroll_periods WHERE month = :month AND year = :year LIMIT 1");
        $stmt->execute(['month' => $month, 'year' => $year]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function createPeriod(string $name, int $month, int $year): int
    {
        $stmt = $this->db->prepare("INSERT INTO tbl_payroll_periods (name, month, year, status, created_at) VALUES (:name, :month, :year, 'draft', NOW())");
        $stmt->execute(['name' => $name, 'month' => $month, 'year' => $year]);
        return (int) $this->db->lastInsertId();
    }

    public function updatePeriodStatus(int $periodId, string $status): bool
    {
        $sql = "UPDATE tbl_payroll_periods SET status = :status";
        $params = ['status' => $status, 'id' => $periodId];

        if ($status === 'approved') {
            $sql .= ", approved_at = NOW()";
        } elseif ($status === 'paid') {
            $sql .= ", processed_at = NOW()";
        }

        $sql .= " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function updatePeriodTotals(int $periodId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE tbl_payroll_periods p
            SET 
                total_employees = (SELECT COUNT(*) FROM tbl_payroll_records WHERE payroll_period_id = p.id),
                total_amount = (SELECT SUM(net_salary) FROM tbl_payroll_records WHERE payroll_period_id = p.id),
                updated_at = NOW()
            WHERE p.id = :id
        ");
        return $stmt->execute(['id' => $periodId]);
    }

    /* ================= PAYROLL RECORDS ================= */

    public function createOrUpdateRecord(array $data): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM tbl_payroll_records WHERE payroll_period_id = :period_id AND employee_id = :employee_id LIMIT 1");
        $stmt->execute(['period_id' => $data['payroll_period_id'], 'employee_id' => $data['employee_id']]);
        $existingId = $stmt->fetchColumn();

        $params = [
            'base_salary'            => $data['base_salary'] ?? 0,
            'total_allowances'       => $data['total_allowances'] ?? 0,
            'overtime_hours'         => $data['overtime_hours'] ?? 0,
            'overtime_amount'        => $data['overtime_amount'] ?? 0,
            'unpaid_leave_days'      => $data['unpaid_leave_days'] ?? 0,
            'unpaid_leave_deduction' => $data['unpaid_leave_deduction'] ?? 0,
            'tax_amount'             => $data['tax_amount'] ?? 0,
            'other_deductions'       => $data['other_deductions'] ?? 0,
            'net_salary'             => $data['net_salary'] ?? 0,
        ];

        if ($existingId) {
            $sql = "UPDATE tbl_payroll_records SET 
                    base_salary = :base_salary,
                    total_allowances = :total_allowances,
                    overtime_hours = :overtime_hours,
                    overtime_amount = :overtime_amount,
                    unpaid_leave_days = :unpaid_leave_days,
                    unpaid_leave_deduction = :unpaid_leave_deduction,
                    tax_amount = :tax_amount,
                    other_deductions = :other_deductions,
                    net_salary = :net_salary,
                    updated_at = NOW()
                    WHERE id = :id";
            $params['id'] = $existingId;
        } else {
            $sql = "INSERT INTO tbl_payroll_records 
                    (payroll_period_id, employee_id, base_salary, total_allowances, overtime_hours, overtime_amount, unpaid_leave_days, unpaid_leave_deduction, tax_amount, other_deductions, net_salary, created_at)
                    VALUES 
                    (:payroll_period_id, :employee_id, :base_salary, :total_allowances, :overtime_hours, :overtime_amount, :unpaid_leave_days, :unpaid_leave_deduction, :tax_amount, :other_deductions, :net_salary, NOW())";
            $params['payroll_period_id'] = $data['payroll_period_id'];
            $params['employee_id'] = $data['employee_id'];
        }

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function getRecordsByPeriod(int $periodId): array
    {
        $stmt = $this->db->prepare("
            SELECT r.*, e.full_name, e.position, e.department 
            FROM tbl_payroll_records r
            JOIN tbl_employees e ON e.id = r.employee_id
            WHERE r.payroll_period_id = :period_id
        ");
        $stmt->execute(['period_id' => $periodId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRecord(int $periodId, int $employeeId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM tbl_payroll_records WHERE payroll_period_id = :period_id AND employee_id = :employee_id LIMIT 1");
        $stmt->execute(['period_id' => $periodId, 'employee_id' => $employeeId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
