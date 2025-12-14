<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateTblLeaveTypesTable extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('tbl_leave_types');
        $table
            ->addColumn('uuid', 'char', ['length' => 36])
            ->addColumn('name', 'string', ['limit' => 50])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('status_id', 'integer', ['limit' => 1, 'default' => 1])
            ->addColumn('created_at', 'datetime')
            ->addColumn('created_by', 'integer', ['null' => true])
            ->addColumn('updated_at', 'datetime', ['null' => true])
            ->addColumn('updated_by', 'integer', ['null' => true])
            ->addColumn('deleted_at', 'datetime', ['null' => true])
            ->addColumn('deleted_by', 'integer', ['null' => true])
            ->addIndex(['uuid'], ['unique' => true])
            ->create();

        // Default leave types
        $this->table('tbl_leave_types')->insert([
            [
                'uuid' => bin2hex(random_bytes(16)),
                'name' => 'Sick Leave',
                'description' => 'Leave for illness',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'uuid' => bin2hex(random_bytes(16)),
                'name' => 'Casual Leave',
                'description' => 'Personal reasons',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'uuid' => bin2hex(random_bytes(16)),
                'name' => 'Annual Leave',
                'description' => 'Yearly paid leave',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'uuid' => bin2hex(random_bytes(16)),
                'name' => 'Other',
                'description' => 'Other leave types',
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ])->saveData();
    }

    public function down(): void
    {
        $this->table('tbl_leave_types')->drop()->save();
    }
}
