<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RefactorEmployeeAuthFields extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('tbl_employees');

        if ($table->hasColumn('user_id')) {
            $table->removeColumn('user_id')->save();
        }

        if (!$table->hasColumn('password')) {
            $table->addColumn('password', 'string', ['limit' => 255, 'null' => true])->save();
        }
    }

    public function down(): void
    {
        $table = $this->table('tbl_employees');

        if ($table->hasColumn('password')) {
            $table->removeColumn('password')->save();
        }

        if (!$table->hasColumn('user_id')) {
            $table->addColumn('user_id', 'integer', ['null' => true])->save();
        }
    }
}
