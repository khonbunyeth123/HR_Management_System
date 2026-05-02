<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateAccessTokensTable extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('tbl_access_tokens');
        $table
            ->addColumn('token', 'string', ['limit' => 255])
            ->addColumn('tokenable_type', 'string', ['limit' => 20])
            ->addColumn('tokenable_id', 'integer')
            ->addColumn('expires_at', 'datetime', ['null' => true])
            ->addColumn('last_used_at', 'datetime', ['null' => true])
            ->addColumn('revoked_at', 'datetime', ['null' => true])
            ->addColumn('created_at', 'datetime')
            ->addIndex(['token'], ['unique' => true])
            ->addIndex(['tokenable_type', 'tokenable_id'])
            ->create();
    }

    public function down(): void
    {
        $this->table('tbl_access_tokens')->drop()->save();
    }
}
