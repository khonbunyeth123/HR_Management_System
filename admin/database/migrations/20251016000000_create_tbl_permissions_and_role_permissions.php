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
        $now = date('Y-m-d H:i:s');

        $permissions = [
            ['module' => 'dashboard',   'action' => 'view',         'description' => 'View dashboard'],
            ['module' => 'attendance',  'action' => 'view',         'description' => 'View attendance'],
            ['module' => 'employee',    'action' => 'view',         'description' => 'View employees'],
            ['module' => 'leave',       'action' => 'view',         'description' => 'View leave requests'],

            ['module' => 'report',      'action' => 'view',         'description' => 'View reports menu'],
            ['module' => 'report',      'action' => 'view_daily',   'description' => 'View daily report'],
            ['module' => 'report',      'action' => 'view_summary', 'description' => 'View summary report'],
            ['module' => 'report',      'action' => 'view_detail',  'description' => 'View detailed report'],
            ['module' => 'report',      'action' => 'view_top',     'description' => 'View top employees report'],

            ['module' => 'user',        'action' => 'view',         'description' => 'View user management'],
            ['module' => 'user',        'action' => 'create',       'description' => 'Create user'],
            ['module' => 'user',        'action' => 'update',       'description' => 'Update user'],
            ['module' => 'user',        'action' => 'delete',       'description' => 'Delete user'],

            ['module' => 'roles',       'action' => 'view',         'description' => 'View roles'],
            ['module' => 'permissions', 'action' => 'view',         'description' => 'View permissions'],
            ['module' => 'audits',      'action' => 'view',         'description' => 'View audit logs'],
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
        $allPerms = $this->fetchAll('SELECT id, module, action FROM tbl_permissions');
        $permMap  = [];
        foreach ($allPerms as $row) {
            $permMap[$row['module'] . '.' . $row['action']] = (int) $row['id'];
        }

        $rolePermissions = [];

        // Resolve role IDs by name (safer than assuming 1/2/3)
        $roles = $this->fetchAll("SELECT id, name FROM tbl_roles");
        $roleIdByName = [];
        foreach ($roles as $r) {
            $roleIdByName[strtolower($r['name'])] = (int) $r['id'];
        }

        $adminId = $roleIdByName['admin'] ?? null;
        $managerId = $roleIdByName['manager'] ?? null;
        $employeeId = $roleIdByName['employee'] ?? null;

        // Admin — everything
        if ($adminId) {
            foreach ($permMap as $pid) {
                $rolePermissions[] = ['role_id' => $adminId, 'permission_id' => $pid];
            }
        }

        // Manager
        $managerPerms = [
            'dashboard.view',
            'attendance.view',
            'employee.view',
            'leave.view',
            'report.view', 'report.view_daily', 'report.view_summary',
            'report.view_detail', 'report.view_top',
        ];
        if ($managerId) {
            foreach ($managerPerms as $key) {
                if (isset($permMap[$key])) {
                    $rolePermissions[] = ['role_id' => $managerId, 'permission_id' => $permMap[$key]];
                }
            }
        }

        // Employee
        $employeePerms = ['dashboard.view', 'attendance.view', 'leave.view'];
        if ($employeeId) {
            foreach ($employeePerms as $key) {
                if (isset($permMap[$key])) {
                    $rolePermissions[] = ['role_id' => $employeeId, 'permission_id' => $permMap[$key]];
                }
            }
        }

        if (!empty($rolePermissions)) {
            $this->table('tbl_role_permissions')->insert($rolePermissions)->saveData();
        }
    }

    public function down(): void
    {
        $this->table('tbl_role_permissions')->drop()->save();
        $this->table('tbl_permissions')->drop()->save();
    }
}
