<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateTblRolesTable extends AbstractMigration
{
    public function up(): void
    {
        // 1️⃣ Create tbl_roles table
        $table = $this->table('tbl_roles');
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

        // 2️⃣ Insert default roles
        $roles = [
            [
                'uuid' => bin2hex(random_bytes(16)),
                'name' => 'Admin',
                'description' => 'System administrator with full access',
                'status_id' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => null,
            ],
            [
                'uuid' => bin2hex(random_bytes(16)),
                'name' => 'Manager',
                'description' => 'Manager with limited access',
                'status_id' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => null,
            ],
            [
                'uuid' => bin2hex(random_bytes(16)),
                'name' => 'Employee',
                'description' => 'Regular employee user',
                'status_id' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => null,
            ],
        ];

        $this->table('tbl_roles')->insert($roles)->saveData();
    }

    public function down(): void
    {
        // Drop table including all default data
        $this->table('tbl_roles')->drop()->save();
    }
}
