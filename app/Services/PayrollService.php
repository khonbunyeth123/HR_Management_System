<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Payroll;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Leave;

class PayrollService
{
    private const WORKING_HOURS_PER_MONTH = 208; // 8 hours * 26 days
    private const WORKING_DAYS_PER_MONTH = 26;

    public function __construct(
        private readonly Payroll $payrollModel,
        private readonly Employee $employeeModel,
        private readonly Attendance $attendanceModel,
        private readonly Leave $leaveModel
    ) {}

    public function getSalaryConfig(int $employeeId): ?array
    {
        return $this->payrollModel->getSalaryConfigByEmployeeId($employeeId);
    }

    public function saveSalaryConfig(int $employeeId, array $data): bool
    {
        return $this->payrollModel->updateSalaryConfig($employeeId, $data);
    }

    public function generateMonthlyPayroll(int $month, int $year): array
    {
        $period = $this->payrollModel->getPeriod($month, $year);
        if (!$period) {
            $name = date('F Y', mktime(0, 0, 0, $month, 1, $year));
            $periodId = $this->payrollModel->createPeriod($name, $month, $year);
        } else {
            $periodId = (int)$period['id'];
            if ($period['status'] !== 'draft') {
                return ['success' => false, 'error' => 'Payroll for this period is already ' . $period['status']];
            }
        }

        $employees = $this->employeeModel->getAll();
        foreach ($employees as $employee) {
            $this->processEmployeePayroll($periodId, (int)$employee['id'], $month, $year);
        }

        $this->payrollModel->updatePeriodTotals($periodId);

        return ['success' => true, 'period_id' => $periodId];
    }

    private function processEmployeePayroll(int $periodId, int $employeeId, int $month, int $year): void
    {
        $config = $this->getSalaryConfig($employeeId);
        if (!$config) {
            return; // Skip if no salary config
        }

        $baseSalary = (float)$config['base_salary'];
        $allowances = (float)$config['allowance_transport'] + (float)$config['allowance_food'] + (float)$config['allowance_phone'] + (float)$config['allowance_other'];

        // Calculate Overtime
        $otHours = $this->attendanceModel->getMonthlyOvertimeHours($employeeId, $month, $year);
        $hourlyRate = $baseSalary / self::WORKING_HOURS_PER_MONTH;
        $otAmount = $otHours * $hourlyRate * 1.5;

        // Calculate Unpaid Leave Deductions
        $unpaidDays = $this->leaveModel->getMonthlyUnpaidDays($employeeId, $month, $year);
        $dailyRate = $baseSalary / self::WORKING_DAYS_PER_MONTH;
        $unpaidDeduction = $unpaidDays * $dailyRate;

        // For now, let's keep tax and other deductions at 0
        $taxAmount = 0;
        $otherDeductions = 0;

        $netSalary = ($baseSalary + $allowances + $otAmount) - ($unpaidDeduction + $taxAmount + $otherDeductions);

        $this->payrollModel->createOrUpdateRecord([
            'payroll_period_id'      => $periodId,
            'employee_id'            => $employeeId,
            'base_salary'            => $baseSalary,
            'total_allowances'       => $allowances,
            'overtime_hours'         => $otHours,
            'overtime_amount'        => $otAmount,
            'unpaid_leave_days'      => $unpaidDays,
            'unpaid_leave_deduction' => $unpaidDeduction,
            'tax_amount'             => $taxAmount,
            'other_deductions'       => $otherDeductions,
            'net_salary'             => $netSalary
        ]);
    }

    public function getPayrollSummary(int $month, int $year): array
    {
        $period = $this->payrollModel->getPeriod($month, $year);
        if (!$period) {
            return ['period' => null, 'records' => []];
        }

        $records = $this->payrollModel->getRecordsByPeriod((int)$period['id']);
        return [
            'period'  => $period,
            'records' => $records
        ];
    }

    public function approvePayroll(int $periodId): bool
    {
        return $this->payrollModel->updatePeriodStatus($periodId, 'approved');
    }
}
