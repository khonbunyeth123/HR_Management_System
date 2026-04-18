<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateTblPermissionsAndRolePermissions extends AbstractMigration
{
    public function up(): void
    {
        // ─── 1. tbl_permissions ───────────────────────────────────────────
        $table = $this->table('tbl_permissions', ['signed' => false]);
        $table
            ->addColumn('uuid',        'char',    ['length' => 36])
            ->addColumn('module',      'string',  ['limit'  => 50])
            ->addColumn('action',      'string',  ['limit'  => 50])
            ->addColumn('description', 'text',    ['null'   => true])
            ->addColumn('status_id',   'integer', ['limit'  => 1, 'default' => 1])
            ->addColumn('created_at',  'datetime')
            ->addColumn('created_by',  'integer', ['null' => true])
            ->addColumn('updated_at',  'datetime', ['null' => true])
            ->addColumn('updated_by',  'integer', ['null' => true])
            ->addColumn('deleted_at',  'datetime', ['null' => true])
            ->addColumn('deleted_by',  'integer', ['null' => true])
            ->addIndex(['uuid'],             ['unique' => true])
            ->addIndex(['module', 'action'], ['unique' => true])
            ->create();

        // ─── 2. tbl_role_permissions ──────────────────────────────────────
        $pivot = $this->table('tbl_role_permissions', ['signed' => false]);
        $pivot
            ->addColumn('role_id',       'integer', ['signed' => false])
            ->addColumn('permission_id', 'integer', ['signed' => false])
            ->addForeignKey('role_id',       'tbl_roles',       'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('permission_id', 'tbl_permissions', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addIndex(['role_id', 'permission_id'], ['unique' => true])
            ->create();

        // ─── 3. Seed permissions ──────────────────────────────────────────
        // module = sidebar section,  action = what they can do
        $now = date('Y-m-d H:i:s');

        $permissions = [
            // Dashboard
            ['module' => 'dashboard',   'action' => 'view',   'description' => 'View dashboard'],

            // Attendance
            ['module' => 'attendance',  'action' => 'view',   'description' => 'View attendance'],

            // Employees
            ['module' => 'employee',    'action' => 'view',   'description' => 'View employees'],

            // Leave
            ['module' => 'leave',       'action' => 'view',   'description' => 'View leave requests'],

            // Reports (submenu)
            ['module' => 'report',      'action' => 'view',          'description' => 'View reports menu'],
            ['module' => 'report',      'action' => 'view_daily',    'description' => 'View daily report'],
            ['module' => 'report',      'action' => 'view_summary',  'description' => 'View summary report'],
            ['module' => 'report',      'action' => 'view_detail',   'description' => 'View detailed report'],
            ['module' => 'report',      'action' => 'view_top',      'description' => 'View top employees report'],

            // Security (submenu)
            ['module' => 'user',        'action' => 'view',   'description' => 'View user management'],
            ['module' => 'roles',       'action' => 'view',   'description' => 'View roles'],
            ['module' => 'permissions', 'action' => 'view',   'description' => 'View permissions'],
            ['module' => 'audits',      'action' => 'view',   'description' => 'View audit logs'],
        ];

        $rows = [];
        foreach ($permissions as $p) {
            $rows[] = [
                'uuid'        => sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                    mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                    mt_rand(0, 0xffff),
                    mt_rand(0, 0x0fff) | 0x4000,
                    mt_rand(0, 0x3fff) | 0x8000,
                    mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
                ),
                'module'      => $p['module'],
                'action'      => $p['action'],
                'description' => $p['description'],
                'status_id'   => 1,
                'created_at'  => $now,
                'created_by'  => null,
            ];
        }

        $this->table('tbl_permissions')->insert($rows)->saveData();

        // ─── 4. Seed role_permissions ─────────────────────────────────────
        // roles: 1=Admin, 2=Manager, 3=Employee  (seeded in roles migration)
        // Admin  → all permissions
        // Manager → dashboard, attendance, employee, leave, all reports
        // Employee → dashboard, attendance, leave

        // Fetch inserted permission IDs by module+action
        $allPerms = $this->fetchAll('SELECT id, module, action FROM tbl_permissions');
        $permMap  = [];
        foreach ($allPerms as $row) {
            $permMap[$row['module'] . '.' . $row['action']] = (int) $row['id'];
        }

        $rolePermissions = [];

        // Admin (role_id = 1) — everything
        foreach ($permMap as $pid) {
            $rolePermissions[] = ['role_id' => 1, 'permission_id' => $pid];
        }

        // Manager (role_id = 2)
        $managerPerms = [
            'dashboard.view',
            'attendance.view',
            'employee.view',
            'leave.view',
            'report.view', 'report.view_daily', 'report.view_summary',
            'report.view_detail', 'report.view_top',
        ];
        foreach ($managerPerms as $key) {
            if (isset($permMap[$key])) {
                $rolePermissions[] = ['role_id' => 2, 'permission_id' => $permMap[$key]];
            }
        }

        // Employee (role_id = 3)
        $employeePerms = ['dashboard.view', 'attendance.view', 'leave.view'];
        foreach ($employeePerms as $key) {
            if (isset($permMap[$key])) {
                $rolePermissions[] = ['role_id' => 3, 'permission_id' => $permMap[$key]];
            }
        }

        $this->table('tbl_role_permissions')->insert($rolePermissions)->saveData();
    }

    public function down(): void
    {
        $this->table('tbl_role_permissions')->drop()->save();
        $this->table('tbl_permissions')->drop()->save();
    }
}