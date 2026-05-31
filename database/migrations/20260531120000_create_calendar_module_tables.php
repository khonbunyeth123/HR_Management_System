<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateCalendarModuleTables extends AbstractMigration
{
    public function up(): void
    {
        $now = date('Y-m-d H:i:s');

        // -----------------------------------------------------------------
        // Branch support for employee filtering
        // -----------------------------------------------------------------
        $employees = $this->table('tbl_employees');
        if (!$employees->hasColumn('branch')) {
            $employees
                ->addColumn('branch', 'string', [
                    'limit' => 100,
                    'null' => true,
                    'default' => null,
                    'after' => 'department',
                ])
                ->save();
        }

        // -----------------------------------------------------------------
        // Calendar events
        // -----------------------------------------------------------------
        if (!$this->hasTable('tbl_calendar_events')) {
            $table = $this->table('tbl_calendar_events', ['signed' => false]);
            $table
                ->addColumn('uuid', 'char', ['length' => 36])
                ->addColumn('title', 'string', ['limit' => 191])
                ->addColumn('description', 'text', ['null' => true])
                ->addColumn('event_type', 'string', ['limit' => 30])
                ->addColumn('status', 'string', [
                    'limit' => 20,
                    'default' => 'pending',
                    'comment' => 'pending, approved, rejected, cancelled',
                ])
                ->addColumn('start_at', 'datetime')
                ->addColumn('end_at', 'datetime')
                ->addColumn('all_day', 'boolean', ['default' => false])
                ->addColumn('recurrence_rule', 'text', ['null' => true])
                ->addColumn('recurrence_parent_id', 'integer', ['null' => true, 'signed' => false])
                ->addColumn('created_at', 'datetime')
                ->addColumn('created_by', 'integer', ['null' => true])
                ->addColumn('updated_at', 'datetime', ['null' => true])
                ->addColumn('updated_by', 'integer', ['null' => true])
                ->addColumn('approved_at', 'datetime', ['null' => true])
                ->addColumn('approved_by', 'integer', ['null' => true])
                ->addColumn('rejected_at', 'datetime', ['null' => true])
                ->addColumn('rejected_by', 'integer', ['null' => true])
                ->addColumn('cancelled_at', 'datetime', ['null' => true])
                ->addColumn('cancelled_by', 'integer', ['null' => true])
                ->addColumn('deleted_at', 'datetime', ['null' => true])
                ->addColumn('deleted_by', 'integer', ['null' => true])
                ->addIndex(['uuid'], ['unique' => true])
                ->addIndex(['event_type'])
                ->addIndex(['status'])
                ->addIndex(['start_at'])
                ->addIndex(['end_at'])
                ->create();
        }

        // -----------------------------------------------------------------
        // Calendar event targets
        // -----------------------------------------------------------------
        if (!$this->hasTable('tbl_calendar_event_targets')) {
            $table = $this->table('tbl_calendar_event_targets', ['signed' => false]);
            $table
                ->addColumn('event_id', 'integer', ['signed' => false])
                ->addColumn('target_type', 'string', ['limit' => 20])
                ->addColumn('target_value', 'string', ['limit' => 191])
                ->addColumn('target_label', 'string', ['limit' => 191, 'null' => true])
                ->addColumn('created_at', 'datetime')
                ->addIndex(['event_id'])
                ->addIndex(['target_type'])
                ->addForeignKey('event_id', 'tbl_calendar_events', 'id', [
                    'delete' => 'CASCADE',
                    'update' => 'CASCADE',
                ])
                ->create();
        }

        // -----------------------------------------------------------------
        // Permissions
        // -----------------------------------------------------------------
        if ($this->hasTable('tbl_permissions')) {
            $permissionRows = [
                ['module' => 'calendar', 'action' => 'view', 'description' => 'View admin calendar'],
                ['module' => 'calendar', 'action' => 'manage', 'description' => 'Manage calendar events'],
            ];

            foreach ($permissionRows as $permission) {
                $existing = $this->fetchRow(
                    'SELECT id FROM tbl_permissions WHERE module = ? AND action = ? AND deleted_at IS NULL LIMIT 1',
                    [$permission['module'], $permission['action']]
                );

                if ($existing) {
                    continue;
                }

                $this->execute(
                    'INSERT INTO tbl_permissions (uuid, module, action, description, status_id, created_at)
                     VALUES (?, ?, ?, ?, 1, ?)',
                    [
                        $this->generateUuid(),
                        $permission['module'],
                        $permission['action'],
                        $permission['description'],
                        $now,
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        if ($this->hasTable('tbl_calendar_event_targets')) {
            $this->table('tbl_calendar_event_targets')->drop()->save();
        }

        if ($this->hasTable('tbl_calendar_events')) {
            $this->table('tbl_calendar_events')->drop()->save();
        }

        if ($this->hasTable('tbl_permissions')) {
            $permissionIds = $this->fetchAll(
                "SELECT id FROM tbl_permissions WHERE module = 'calendar' AND deleted_at IS NULL"
            );

            foreach ($permissionIds as $row) {
                $this->execute('DELETE FROM tbl_role_permissions WHERE permission_id = ?', [(int) $row['id']]);
            }

            $this->execute("DELETE FROM tbl_permissions WHERE module = 'calendar'");
        }

        $employees = $this->table('tbl_employees');
        if ($employees->hasColumn('branch')) {
            $employees->removeColumn('branch')->save();
        }
    }

    private function generateUuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
