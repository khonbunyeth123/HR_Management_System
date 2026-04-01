<?php
declare(strict_types=1);
use Phinx\Migration\AbstractMigration;
final class CreateTblPermissionsAndRolePermissions extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('tbl_permissions', ['signed' => false]);
        $table
            ->addColumn('uuid', 'char', ['length' => 36])
            ->addColumn('module', 'string', ['limit' => 50])
            ->addColumn('action', 'string', ['limit' => 50])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('status_id', 'integer', ['limit' => 1, 'default' => 1])
            ->addColumn('created_at', 'datetime')
            ->addColumn('created_by', 'integer', ['null' => true])
            ->addColumn('updated_at', 'datetime', ['null' => true])
            ->addColumn('updated_by', 'integer', ['null' => true])
            ->addColumn('deleted_at', 'datetime', ['null' => true])
            ->addColumn('deleted_by', 'integer', ['null' => true])
            ->addIndex(['uuid'], ['unique' => true])
            ->addIndex(['module', 'action'], ['unique' => true])
            ->create();

        $table = $this->table('tbl_role_permissions', ['signed' => false]);
        $table
            ->addColumn('role_id', 'integer', ['signed' => false])
            ->addColumn('permission_id', 'integer', ['signed' => false])
            ->addForeignKey('role_id', 'tbl_roles', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('permission_id', 'tbl_permissions', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addIndex(['role_id', 'permission_id'], ['unique' => true])
            ->create();
    }

    public function down(): void
    {
        $this->table('tbl_role_permissions')->drop()->save();
        $this->table('tbl_permissions')->drop()->save();
    }
}
