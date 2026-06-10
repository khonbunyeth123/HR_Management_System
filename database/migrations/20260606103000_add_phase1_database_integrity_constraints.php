<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddPhase1DatabaseIntegrityConstraints extends AbstractMigration
{
    public function up(): void
    {
        $this->ensureLeaveAuditTable();
        $this->ensureLeaveCheckType();
    }

    public function down(): void
    {
        $this->execute("DELETE FROM tbl_check_types WHERE LOWER(TRIM(name)) = 'leave'");
    }

    private function ensureLeaveAuditTable(): void
    {
        if (!$this->hasTable('tbl_leave_audit')) {
            $table = $this->table('tbl_leave_audit', ['signed' => false]);
            $table
                ->addColumn('leave_id', 'integer', ['signed' => false])
                ->addColumn('action', 'enum', ['values' => ['approved', 'rejected', 'created', 'cancelled']])
                ->addColumn('performed_by_user_id', 'integer', ['signed' => false])
                ->addColumn('performed_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                ->addColumn('ip_address', 'string', ['limit' => 45, 'null' => true])
                ->addIndex(['leave_id'])
                ->addIndex(['performed_by_user_id'])
                ->create();

            return;
        }

        $table = $this->table('tbl_leave_audit');

        if ($table->hasColumn('performed_by_user_id')) {
            $table->changeColumn('performed_by_user_id', 'integer', ['signed' => false])->update();
        } else {
            $table->addColumn('performed_by_user_id', 'integer', ['signed' => false, 'after' => 'leave_id'])->update();
        }

        if (!$table->hasIndex(['performed_by_user_id'])) {
            $table->addIndex(['performed_by_user_id'])->update();
        }
    }

    private function ensureLeaveCheckType(): void
    {
        $checkType = $this->fetchRow(
            "SELECT id, deleted_at FROM tbl_check_types WHERE LOWER(TRIM(name)) = 'leave' LIMIT 1"
        );

        if ($checkType) {
            $this->execute(
                "UPDATE tbl_check_types
                 SET deleted_at = NULL,
                     deleted_by = NULL,
                     status_id = 1
                 WHERE LOWER(TRIM(name)) = 'leave'"
            );
            return;
        }

        $this->execute(
            "INSERT INTO tbl_check_types (name, standard_time, description, status_id, created_at)
             VALUES ('Leave', NULL, 'Approved leave attendance marker', 1, NOW())"
        );
    }
}
