<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddRejectionFieldsToLeaveApplications extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('tbl_leave_applications');
        $table
            ->addColumn('rejected_by', 'integer', ['null' => true, 'after' => 'approved_at'])
            ->addColumn('rejected_at', 'datetime', ['null' => true, 'after' => 'rejected_by'])
            ->update();
    }

    public function down(): void
    {
        $table = $this->table('tbl_leave_applications');
        $table
            ->removeColumn('rejected_at')
            ->removeColumn('rejected_by')
            ->update();
    }
}
