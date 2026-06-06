<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use App\Support\Uuid;

final class CreateTblRolesTable extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('tbl_roles', ['signed' => false]);
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
            ->addIndex(['name'], ['unique' => true])
            ->create();

        $roles = [
            [
                'uuid' => Uuid::v4(),
                'name' => 'Admin',
                'description' => 'System administrator with full access',
                'status_id' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => null,
            ],
            [
                'uuid' => Uuid::v4(),
                'name' => 'Manager',
                'description' => 'Manager with limited access',
                'status_id' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => null,
            ],
            [
                'uuid' => Uuid::v4(),
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
        $this->table('tbl_roles')->drop()->save();
    }
}
