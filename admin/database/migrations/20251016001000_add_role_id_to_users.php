<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddRoleIdToUsers extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('tbl_users');
        if (!$table->hasColumn('role_id')) {
            $table
                ->addColumn('role_id', 'integer', ['null' => true, 'signed' => false, 'after' => 'role'])
                ->addForeignKey('role_id', 'tbl_roles', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                ->update();
        }
        // Ensure FK exists if role_id was already created earlier
        $table = $this->table('tbl_users');
        if ($table->hasColumn('role_id')) {
            try {
                $table->addForeignKey('role_id', 'tbl_roles', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                      ->update();
            } catch (\Throwable $e) {
                // ignore if FK already exists
            }
        }

        // Backfill role_id from legacy role string (case-insensitive)
        // Map unknown roles (e.g. "user", "guest") to Employee by default.
        $usersTable = $this->table('tbl_users');
        if ($usersTable->hasColumn('role')) {
            $this->execute("
                UPDATE tbl_users u
                JOIN tbl_roles r
                  ON LOWER(r.name) = LOWER(
                    CASE
                      WHEN u.role IN ('user', 'guest') THEN 'Employee'
                      ELSE u.role
                    END
                  )
                SET u.role_id = r.id
                WHERE u.role_id IS NULL
            ");
            $usersTable
                ->removeColumn('role')
                ->update();
        }

        // Ensure seeded test users get correct role_id if missing
        $this->execute("
            UPDATE tbl_users u
            JOIN tbl_roles r ON LOWER(r.name) = 'admin'
            SET u.role_id = r.id
            WHERE u.role_id IS NULL
              AND u.username IN ('admin','testadmin')
        ");
        $this->execute("
            UPDATE tbl_users u
            JOIN tbl_roles r ON LOWER(r.name) = 'manager'
            SET u.role_id = r.id
            WHERE u.role_id IS NULL
              AND u.username IN ('testmanager')
        ");
        $this->execute("
            UPDATE tbl_users u
            JOIN tbl_roles r ON LOWER(r.name) = 'employee'
            SET u.role_id = r.id
            WHERE u.role_id IS NULL
              AND u.username IN ('testemployee')
        ");
    }

    public function down(): void
    {
        $table = $this->table('tbl_users');
        if ($table->hasColumn('role_id')) {
            $table->dropForeignKey('role_id')->save();
            $table->removeColumn('role_id')->update();
        }
    }
}
