<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreatePasswordResetsTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('password_resets', ['id' => false, 'primary_key' => ['email', 'token']]);
        $table
            ->addColumn('email', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('token', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addIndex(['email'])
            ->addIndex(['token'])
            ->create();
    }
}
