<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreatePayrollModuleTables extends AbstractMigration
{
    public function up(): void
    {
        // 1. Salary Configurations
        $salaryConfig = $this->table('tbl_salary_configurations');
        $salaryConfig
            ->addColumn('employee_id', 'integer')
            ->addColumn('base_salary', 'decimal', ['precision' => 15, 'scale' => 2, 'default' => 0.00])
            ->addColumn('allowance_transport', 'decimal', ['precision' => 15, 'scale' => 2, 'default' => 0.00])
            ->addColumn('allowance_food', 'decimal', ['precision' => 15, 'scale' => 2, 'default' => 0.00])
            ->addColumn('allowance_phone', 'decimal', ['precision' => 15, 'scale' => 2, 'default' => 0.00])
            ->addColumn('allowance_other', 'decimal', ['precision' => 15, 'scale' => 2, 'default' => 0.00])
            ->addColumn('created_at', 'datetime')
            ->addColumn('updated_at', 'datetime', ['null' => true])
            ->addIndex(['employee_id'], ['unique' => true])
            ->create();

        // 2. Payroll Periods
        $payrollPeriods = $this->table('tbl_payroll_periods');
        $payrollPeriods
            ->addColumn('name', 'string', ['limit' => 100])
            ->addColumn('month', 'integer', ['limit' => 2])
            ->addColumn('year', 'integer', ['limit' => 4])
            ->addColumn('status', 'enum', ['values' => ['draft', 'approved', 'paid'], 'default' => 'draft'])
            ->addColumn('total_employees', 'integer', ['default' => 0])
            ->addColumn('total_amount', 'decimal', ['precision' => 15, 'scale' => 2, 'default' => 0.00])
            ->addColumn('processed_at', 'datetime', ['null' => true])
            ->addColumn('approved_at', 'datetime', ['null' => true])
            ->addColumn('created_at', 'datetime')
            ->addColumn('updated_at', 'datetime', ['null' => true])
            ->addIndex(['month', 'year'], ['unique' => true])
            ->create();

        // 3. Payroll Records (The individual payslip data)
        $payrollRecords = $this->table('tbl_payroll_records');
        $payrollRecords
            ->addColumn('payroll_period_id', 'integer')
            ->addColumn('employee_id', 'integer')
            ->addColumn('base_salary', 'decimal', ['precision' => 15, 'scale' => 2])
            ->addColumn('total_allowances', 'decimal', ['precision' => 15, 'scale' => 2, 'default' => 0.00])
            ->addColumn('overtime_hours', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0.00])
            ->addColumn('overtime_amount', 'decimal', ['precision' => 15, 'scale' => 2, 'default' => 0.00])
            ->addColumn('unpaid_leave_days', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0.00])
            ->addColumn('unpaid_leave_deduction', 'decimal', ['precision' => 15, 'scale' => 2, 'default' => 0.00])
            ->addColumn('tax_amount', 'decimal', ['precision' => 15, 'scale' => 2, 'default' => 0.00])
            ->addColumn('other_deductions', 'decimal', ['precision' => 15, 'scale' => 2, 'default' => 0.00])
            ->addColumn('net_salary', 'decimal', ['precision' => 15, 'scale' => 2])
            ->addColumn('payment_status', 'enum', ['values' => ['pending', 'paid'], 'default' => 'pending'])
            ->addColumn('created_at', 'datetime')
            ->addColumn('updated_at', 'datetime', ['null' => true])
            ->addIndex(['payroll_period_id'])
            ->addIndex(['employee_id'])
            ->create();
    }

    public function down(): void
    {
        $this->table('tbl_payroll_records')->drop()->save();
        $this->table('tbl_payroll_periods')->drop()->save();
        $this->table('tbl_salary_configurations')->drop()->save();
    }
}
