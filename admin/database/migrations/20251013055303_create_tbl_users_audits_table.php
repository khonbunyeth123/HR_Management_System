<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateTblUsersAuditsTable extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('tbl_users_audits');
        $table
            ->addColumn('user_id', 'integer', ['null' => false])
            ->addColumn('context', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('description', 'text', ['null' => false])
            ->addColumn('audit_type_id', 'integer', ['null' => false])
            ->addColumn('user_agent', 'text', ['null' => false])
            ->addColumn('operator', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('ip', 'string', ['limit' => 45, 'null' => false]) // changed from inet
            ->addColumn('status_id', 'smallinteger', ['default' => 1, 'null' => false])
            ->addColumn('order', 'integer', ['default' => 1, 'null' => false])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addColumn('created_by', 'integer', ['null' => true])
            ->addColumn('updated_at', 'timestamp', ['null' => true])
            ->addColumn('updated_by', 'integer', ['null' => true])
            ->addColumn('deleted_at', 'timestamp', ['null' => true])
            ->addColumn('deleted_by', 'integer', ['null' => true])
            ->create();
    }

    public function down(): void
    {
        $this->table('tbl_users_audits')->drop()->save();
    }
}
