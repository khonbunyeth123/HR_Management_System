<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class DropEmployeeCodeColumnFromTblEmployees extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('tbl_employees');

        if ($table->hasIndex(['employee_id'])) {
            $table->removeIndex(['employee_id'])->save();
        }

        if ($table->hasColumn('employee_id')) {
            $table->removeColumn('employee_id')->save();
        }
    }

    public function down(): void
    {
        $table = $this->table('tbl_employees');

        if (!$table->hasColumn('employee_id')) {
            $table->addColumn('employee_id', 'string', ['limit' => 20, 'null' => true])->save();
        }

        if (!$table->hasIndex(['employee_id'])) {
            $table->addIndex(['employee_id'], ['unique' => true])->save();
        }
    }
}
