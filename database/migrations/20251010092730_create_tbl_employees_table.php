<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateTblEmployeesTable extends AbstractMigration
{
    public function up(): void
    {
        // Let Phinx create the default 'id' column (auto-increment primary key)
        $table = $this->table('tbl_employees');
        $table
            ->addColumn('uuid', 'char', ['length' => 36])
            ->addColumn('user_id', 'integer', ['null' => true]) // link to tbl_users if needed
            ->addColumn('username', 'string', ['limit' => 50]) // username
            ->addColumn('first_name', 'string', ['limit' => 50])
            ->addColumn('last_name', 'string', ['limit' => 50])
            ->addColumn('full_name', 'string', ['limit' => 100])
            ->addColumn('position', 'string', ['limit' => 50])
            ->addColumn('department', 'string', ['limit' => 50])
            ->addColumn('date_hired', 'date')
            ->addColumn('status_id', 'integer', ['limit' => 1, 'default' => 1])
            ->addColumn('created_at', 'datetime')
            ->addColumn('created_by', 'integer', ['null' => true])
            ->addColumn('updated_at', 'datetime', ['null' => true])
            ->addColumn('updated_by', 'integer', ['null' => true])
            ->addColumn('deleted_at', 'datetime', ['null' => true])
            ->addColumn('deleted_by', 'integer', ['null' => true])
            ->addIndex(['uuid'], ['unique' => true])
            ->addIndex(['username'], ['unique' => true])
            ->create();

        // Insert default employee
        $employeeData = [
            [
                'uuid' => bin2hex(random_bytes(16)),
                'user_id' => 1, // assuming admin user exists
                'username' => 'admin',
                'first_name' => 'Admin',
                'last_name' => 'User',
                'full_name' => 'Admin User',
                'position' => 'System Administrator',
                'department' => 'IT',
                'date_hired' => date('Y-m-d'),
                'status_id' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => 1,
            ]
        ];

        $this->table('tbl_employees')->insert($employeeData)->saveData();
    }

    public function down(): void
    {
        $this->table('tbl_employees')->drop()->save();
    }
}
