<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddMissingEmployeeProfileColumns extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('tbl_employees');
        $changed = false;

        if (!$table->hasColumn('gender')) {
            $table->addColumn('gender', 'string', ['limit' => 10, 'null' => true, 'after' => 'full_name']);
            $changed = true;
        }

        if (!$table->hasColumn('email')) {
            $table->addColumn('email', 'string', ['limit' => 100, 'null' => true, 'after' => 'gender']);
            $changed = true;
        }

        if (!$table->hasColumn('address')) {
            $table->addColumn('address', 'text', ['null' => true, 'after' => 'email']);
            $changed = true;
        }

        if (!$table->hasColumn('dob')) {
            $table->addColumn('dob', 'date', ['null' => true, 'after' => 'address']);
            $changed = true;
        }

        if ($changed) {
            $table->update();
        }

        if ($this->hasTable('tbl_users') && $this->table('tbl_employees')->hasColumn('email')) {
            $this->execute(
                "UPDATE tbl_employees e
                 LEFT JOIN tbl_users u ON u.id = e.user_id
                 SET e.email = COALESCE(NULLIF(e.email, ''), u.email)
                 WHERE (e.email IS NULL OR e.email = '')"
            );
        }
    }

    public function down(): void
    {
        $table = $this->table('tbl_employees');
        $changed = false;

        foreach (['dob', 'address', 'email', 'gender'] as $column) {
            if ($table->hasColumn($column)) {
                $table->removeColumn($column);
                $changed = true;
            }
        }

        if ($changed) {
            $table->update();
        }
    }
}