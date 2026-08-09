<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use App\Support\Uuid;

final class CreateAttendanceLocationsTable extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('tbl_attendance_locations', ['signed' => false]);
        $table
            ->addColumn('name', 'string', ['limit' => 255])
            ->addColumn('latitude', 'decimal', ['precision' => 10, 'scale' => 8])
            ->addColumn('longitude', 'decimal', ['precision' => 11, 'scale' => 8])
            ->addColumn('radius', 'integer')
            ->addColumn('status', 'string', ['limit' => 20, 'default' => 'inactive'])
            ->addColumn('created_at', 'datetime')
            ->addColumn('updated_at', 'datetime', ['null' => true])
            ->addIndex(['status'])
            ->create();

        $this->seedPermission();
        $this->seedInitialLocation();
    }

    public function down(): void
    {
        $permission = $this->fetchRow(
            "SELECT id FROM tbl_permissions WHERE module = 'attendance' AND action = 'manage_location' LIMIT 1"
        );

        if (!empty($permission['id'])) {
            $this->execute(
                'DELETE FROM tbl_role_permissions WHERE permission_id = ' . (int) $permission['id']
            );
            $this->execute(
                'DELETE FROM tbl_permissions WHERE id = ' . (int) $permission['id']
            );
        }

        $this->table('tbl_attendance_locations')->drop()->save();
    }

    private function seedPermission(): void
    {
        $existing = $this->fetchRow(
            "SELECT id FROM tbl_permissions WHERE module = 'attendance' AND action = 'manage_location' LIMIT 1"
        );

        if (!empty($existing['id'])) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $permissionUuid = Uuid::v4();

        $this->execute(
            "INSERT INTO tbl_permissions (uuid, module, action, description, status_id, created_at, created_by)
             VALUES ('{$permissionUuid}', 'attendance', 'manage_location', 'Manage attendance locations', 1, '{$now}', NULL)"
        );

        $permission = $this->fetchRow(
            "SELECT id FROM tbl_permissions WHERE module = 'attendance' AND action = 'manage_location' LIMIT 1"
        );
        $permissionId = (int) ($permission['id'] ?? 0);

        if ($permissionId > 0) {
            $this->execute(
                'INSERT IGNORE INTO tbl_role_permissions (role_id, permission_id) VALUES (1, ' . $permissionId . ')'
            );
        }
    }

    private function seedInitialLocation(): void
    {
        $existing = $this->fetchRow('SELECT id FROM tbl_attendance_locations LIMIT 1');
        if (!empty($existing['id'])) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $this->execute(
            "INSERT INTO tbl_attendance_locations (name, latitude, longitude, radius, status, created_at, updated_at)
             VALUES ('Sangkat Srah Chak', 11.58091400, 104.90983200, 100, 'active', '{$now}', '{$now}')"
        );
    }
}
