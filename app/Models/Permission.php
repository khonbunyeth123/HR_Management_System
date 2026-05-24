<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Permission
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAllPermissions(): array
    {
        $stmt = $this->db->query('SELECT * FROM tbl_permissions WHERE deleted_at IS NULL ORDER BY module ASC, action ASC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRolePermissions(int $roleId): array
    {
        $stmt = $this->db->prepare(" 
            SELECT p.*
            FROM tbl_permissions p
            INNER JOIN tbl_role_permissions rp ON rp.permission_id = p.id
            WHERE rp.role_id = ?
            ORDER BY p.module ASC, p.action ASC
        ");

        $stmt->execute([$roleId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function assignPermissionsToRole(int $roleId, array $permissions): bool
    {
        $this->db->beginTransaction();

        try {
            $deleteStmt = $this->db->prepare('DELETE FROM tbl_role_permissions WHERE role_id = ?');
            $deleteStmt->execute([$roleId]);

            $permissionIds = array_values(array_unique(array_map('intval', $permissions)));
            $permissionIds = array_filter($permissionIds, static fn (int $id): bool => $id > 0);

            if (!empty($permissionIds)) {
                $insertStmt = $this->db->prepare('INSERT INTO tbl_role_permissions (role_id, permission_id) VALUES (?, ?)');

                foreach ($permissionIds as $permissionId) {
                    $insertStmt->execute([$roleId, $permissionId]);
                }
            }

            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }
}
