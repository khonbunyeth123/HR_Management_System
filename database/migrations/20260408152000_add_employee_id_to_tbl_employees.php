<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddEmployeeIdToTblEmployees extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('tbl_employees');
        $changed = false;

        if (!$table->hasColumn('employee_id')) {
            $table->addColumn('employee_id', 'string', ['limit' => 20, 'null' => true, 'after' => 'id']);
            $changed = true;
        }

        if ($changed) {
            $table->update();
            $table = $this->table('tbl_employees');
        }

        if ($table->hasColumn('employee_id')) {
            $this->execute(
                "UPDATE tbl_employees
                 SET employee_id = CONCAT('EMP', LPAD(id, 5, '0'))
                 WHERE employee_id IS NULL OR employee_id = ''"
            );

            if (!$table->hasIndex(['employee_id'])) {
                $table
                    ->addIndex(['employee_id'], ['unique' => true])
                    ->update();
            }
        }
    }

    public function down(): void
    {
        $table = $this->table('tbl_employees');
        $changed = false;

        if ($table->hasIndex(['employee_id'])) {
            $table->removeIndex(['employee_id']);
            $changed = true;
        }

        if ($table->hasColumn('employee_id')) {
            $table->removeColumn('employee_id');
            $changed = true;
        }

        if ($changed) {
            $table->update();
        }
    }
}
