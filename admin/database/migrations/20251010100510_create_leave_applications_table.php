<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateTblLeaveApplicationsTable extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('tbl_leave_applications');
        $table
            ->addColumn('uuid', 'char', ['length' => 36])
            ->addColumn('employee_id', 'integer')
            ->addColumn('leave_type_id', 'integer')
            ->addColumn('start_date', 'date')
            ->addColumn('end_date', 'date')
            ->addColumn('reason', 'text')
            ->addColumn('status_id', 'integer', [
                'limit' => 1,
                'default' => 0,
                'comment' => '0=pending, 1=approved, 2=rejected'
            ])
            ->addColumn('remark', 'text', ['null' => true, 'after' => 'status_id']) // <-- added
            ->addColumn('approved_by', 'integer', ['null' => true])
            ->addColumn('approved_at', 'datetime', ['null' => true])
            ->addColumn('created_at', 'datetime')
            ->addColumn('created_by', 'integer', ['null' => true])
            ->addColumn('updated_at', 'datetime', ['null' => true])
            ->addColumn('updated_by', 'integer', ['null' => true])
            ->addColumn('deleted_at', 'datetime', ['null' => true])
            ->addColumn('deleted_by', 'integer', ['null' => true])
            ->addIndex(['uuid'], ['unique' => true])
            ->addIndex(['employee_id'])
            ->addIndex(['leave_type_id'])
            ->addIndex(['status_id'])
            ->create();
    }

    public function down(): void
    {
        $this->table('tbl_leave_applications')->drop()->save();
    }
}
