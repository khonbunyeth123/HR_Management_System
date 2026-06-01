<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Migration to create tbl_leave_audit and add indexes to tbl_leave_applications.
 */
class LeaveRefactorMigration extends AbstractMigration
{
    public function change(): void
    {
        // Improvement 3: Create tbl_leave_audit
        $table = $this->table('tbl_leave_audit');
        $table->addColumn('leave_id', 'integer')
              ->addColumn('action', 'enum', ['values' => ['approved', 'rejected', 'created', 'cancelled']])
              ->addColumn('performed_by_user_id', 'integer')
              ->addColumn('performed_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
              ->addColumn('ip_address', 'string', ['limit' => 45, 'null' => true])
              ->addForeignKey('leave_id', 'tbl_leave_applications', 'id', ['delete'=> 'CASCADE', 'update'=> 'NO_ACTION'])
              ->create();

        // Improvement 5: SQL indexes
        $leaveApplications = $this->table('tbl_leave_applications');
        
        // Composite index for attendance UNION ALL (or replacement query)
        $leaveApplications->addIndex(['status_id', 'start_date', 'end_date', 'employee_id'], ['name' => 'idx_leave_attendance'])
                          // Index for dashboard today-on-leave
                          ->addIndex(['status_id', 'start_date'], ['name' => 'idx_leave_dashboard'])
                          // Index for employee history
                          ->addIndex(['employee_id', 'created_at'], ['name' => 'idx_leave_history'])
                          ->update();
    }
}
