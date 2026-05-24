<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddFcmTokenToTblEmployees extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('tbl_employees');

        if (!$table->hasColumn('fcm_token')) {
            $table
                ->addColumn('fcm_token', 'string', [
                    'limit'   => 255,
                    'null'    => true,
                    'default' => null,
                    'after'   => 'password',
                ])
                ->save();
        }
    }

    public function down(): void
    {
        $table = $this->table('tbl_employees');

        if ($table->hasColumn('fcm_token')) {
            $table->removeColumn('fcm_token')->save();
        }
    }
}
