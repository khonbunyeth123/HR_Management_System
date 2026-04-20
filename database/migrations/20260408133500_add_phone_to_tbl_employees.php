<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddPhoneToTblEmployees extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('tbl_employees');

        if (!$table->hasColumn('phone')) {
            $table
                ->addColumn('phone', 'string', ['limit' => 20, 'null' => true, 'after' => 'email'])
                ->update();
        }
    }

    public function down(): void
    {
        $table = $this->table('tbl_employees');

        if ($table->hasColumn('phone')) {
            $table
                ->removeColumn('phone')
                ->update();
        }
    }
}