<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateAttendanceRecordsTable extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('tbl_attendance_records');
        $table
            ->addColumn('uuid', 'char', ['length' => 36])
            ->addColumn('employee_id', 'integer')
            ->addColumn('date', 'date')
            ->addColumn('check_time', 'time') // time of check-in/check-out
            ->addColumn('check_type_id', 'integer') // foreign key to tbl_check_types
            ->addColumn('status_id', 'integer', ['limit' => 1, 'default' => 1])
            ->addColumn('created_at', 'datetime')
            ->addColumn('created_by', 'integer', ['null' => true])
            ->addColumn('updated_at', 'datetime', ['null' => true])
            ->addColumn('updated_by', 'integer', ['null' => true])
            ->addColumn('deleted_at', 'datetime', ['null' => true])
            ->addColumn('deleted_by', 'integer', ['null' => true])
            // ->addForeignKey('employee_id', 'tbl_employees', 'id', ['delete'=> 'CASCADE', 'update'=> 'NO_ACTION'])
            // ->addForeignKey('check_type_id', 'tbl_check_types', 'id', ['delete'=> 'NO_ACTION', 'update'=> 'NO_ACTION'])
            ->addIndex(['uuid'], ['unique' => true])
            ->addIndex(['employee_id', 'date', 'check_type_id'], ['unique' => true])
            ->create();
    }

    public function down(): void
    {
        $this->table('tbl_attendance_records')->drop()->save();
    }
}
