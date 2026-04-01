<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateTblCheckTypesTable extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('tbl_check_types');
        $table
            ->addColumn('name', 'string', ['limit' => 50])
            ->addColumn('standard_time', 'time', ['null' => true]) // standard check-in/out time
            ->addColumn('description', 'string', ['limit' => 100, 'null' => true]) // explanation
            ->addColumn('status_id', 'integer', ['limit' => 1, 'default' => 1])
            ->addColumn('created_at', 'datetime')
            ->addColumn('created_by', 'integer', ['null' => true])
            ->addColumn('updated_at', 'datetime', ['null' => true])
            ->addColumn('updated_by', 'integer', ['null' => true])
            ->addColumn('deleted_at', 'datetime', ['null' => true])
            ->addColumn('deleted_by', 'integer', ['null' => true])
            ->create();

        // Insert default check types with standard times
        $checkTypes = [
            ['name' => 'Check-in 1', 'standard_time' => '08:00:00', 'description' => 'Start of shift', 'status_id' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Check-out 1', 'standard_time' => '12:00:00', 'description' => 'Lunch break start', 'status_id' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Check-in 2', 'standard_time' => '13:00:00', 'description' => 'Lunch break end', 'status_id' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Check-out 2', 'standard_time' => '17:00:00', 'description' => 'End of shift', 'status_id' => 1, 'created_at' => date('Y-m-d H:i:s')],
        ];

        $this->table('tbl_check_types')->insert($checkTypes)->saveData();
    }

    public function down(): void
    {
        $this->table('tbl_check_types')->drop()->save();
    }
}
