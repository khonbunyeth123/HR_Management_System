<?php

namespace App\Models;
use App\Core\Database;
use PDO;

class Role
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function getAllRoles($includeDeleted = false)
    {
        $query = "SELECT 
                    r.id,
                    r.uuid,
                    r.name,
                    r.description,
                    r.status_id,
                    r.created_at,
                    r.created_by,
                    r.updated_at,
                    r.updated_by,
                    COUNT(DISTINCT u.id) as user_count,
                    COUNT(DISTINCT rp.permission_id) as permission_count
                FROM tbl_roles r
                LEFT JOIN tbl_users u 
                    ON r.id = u.role_id AND u.deleted_at IS NULL
                LEFT JOIN tbl_role_permissions rp 
                    ON r.id = rp.role_id";

        if (!$includeDeleted) {
            $query .= " WHERE r.deleted_at IS NULL";
        }

        $query .= " GROUP BY r.id ORDER BY r.created_at DESC";

        $result = $this->pdo->query($query);
        return $result->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getRoleById($id)
    {
        $query = "SELECT 
                    r.id,   
                    r.uuid,
                    r.name,
                    r.description,
                    r.status_id,
                    r.created_at,
                    r.created_by,
                    r.updated_at,
                    r.updated_by,
                    COUNT(DISTINCT u.id) as user_count
                FROM tbl_roles r
                LEFT JOIN tbl_users u ON r.id = u.role_id AND u.deleted_at IS NULL
                WHERE r.id = ? AND r.deleted_at IS NULL
                GROUP BY r.id";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$id]);
        $role = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$role) return null;

        $permissions = $this->getRolePermissions($id);
        $role['permissions'] = array_map(function ($p) {
            return $p['permission_slug'];
        }, $permissions);

        return $role;
    }

    public function getRolePermissions($roleId)
    {
        $query = "SELECT 
                    p.id,
                    p.uuid,
                    p.module,
                    p.action,
                    p.description,
                    CONCAT(p.module, '.', p.action) as permission_slug
                  FROM tbl_permissions p
                  INNER JOIN tbl_role_permissions rp ON p.id = rp.permission_id
                  WHERE rp.role_id = ? AND p.status_id = 1 AND p.deleted_at IS NULL
                  ORDER BY p.module, p.action";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$roleId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function createRole($data)
    {
        $uuid = $this->generateUuid();
        $now = date('Y-m-d H:i:s');
        $createdBy = $_SESSION['user_id'] ?? null;

        $query = "INSERT INTO tbl_roles (uuid, name, description, status_id, created_at, created_by)
                  VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            $uuid,
            $data['name'] ?? '',
            $data['description'] ?? null,
            $data['status_id'] ?? 2,
            $now,
            $createdBy
        ]);

        return $this->pdo->lastInsertId();
    }

    public function updateRole($id, $data)
    {
        $now = date('Y-m-d H:i:s');
        $updatedBy = $_SESSION['user_id'] ?? null;

        $fields = [];
        $values = [];

        if (isset($data['name'])) {
            $fields[] = "name = ?";
            $values[] = $data['name'];
        }
        if (isset($data['description'])) {
            $fields[] = "description = ?";
            $values[] = $data['description'];
        }
        if (isset($data['status_id'])) {
            $fields[] = "status_id = ?";
            $values[] = $data['status_id'];
        }

        if (empty($fields)) {
            return false;
        }

        $fields[] = "updated_at = ?";
        $values[] = $now;
        $fields[] = "updated_by = ?";
        $values[] = $updatedBy;
        $values[] = $id;

        $query = "UPDATE tbl_roles SET " . implode(", ", $fields) . " WHERE id = ?";
        
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute($values);
    }

    public function deleteRole($id)
    {
        $now = date('Y-m-d H:i:s');
        $deletedBy = $_SESSION['user_id'] ?? null;

        $query = "UPDATE tbl_roles 
                  SET deleted_at = ?, deleted_by = ? 
                  WHERE id = ? AND deleted_at IS NULL";
        
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute([$now, $deletedBy, $id]);
    }

    public function roleExists($name, $excludeId = null)
    {
        $query = "SELECT COUNT(*) as count FROM tbl_roles WHERE name = ? AND deleted_at IS NULL";
        $params = [$name];
        if ($excludeId) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return ($result['count'] ?? 0) > 0;
    }

    public function getAllPermissions()
    {
        $query = "SELECT id, module, action, CONCAT(module, '.', action) as permission_slug FROM tbl_permissions WHERE deleted_at IS NULL";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPermissionBySlug($slug)
    {
        $query = "SELECT * FROM tbl_permissions WHERE CONCAT(module, '.', action) = ? AND deleted_at IS NULL";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function assignPermission($roleId, $permissionId)
    {
        $checkQuery = "SELECT id FROM tbl_role_permissions WHERE role_id = ? AND permission_id = ?";
        $stmt = $this->pdo->prepare($checkQuery);
        $stmt->execute([$roleId, $permissionId]);
        if ($stmt->fetch()) return false;

        $query = "INSERT INTO tbl_role_permissions (role_id, permission_id) VALUES (?, ?)";
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute([$roleId, $permissionId]);
    }

    public function removePermission($roleId, $permissionId)
    {
        $query = "DELETE FROM tbl_role_permissions WHERE role_id = ? AND permission_id = ?";
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute([$roleId, $permissionId]);
    }

    public function syncPermissions($roleId, $permissionIds = [])
    {
        $deleteQuery = "DELETE FROM tbl_role_permissions WHERE role_id = ?";
        $deleteStmt = $this->pdo->prepare($deleteQuery);
        $deleteStmt->execute([$roleId]);

        if (!empty($permissionIds)) {
            $query = "INSERT INTO tbl_role_permissions (role_id, permission_id) VALUES (?, ?)";
            $stmt = $this->pdo->prepare($query);
            
            foreach ($permissionIds as $permissionId) {
                $stmt->execute([$roleId, $permissionId]);
            }
        }

        return true;
    }

    public function getPermissionCount($roleId)
    {
        $query = "SELECT COUNT(*) as count FROM tbl_role_permissions WHERE role_id = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$roleId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }

    public function getStatusName($statusId)
    {
        $statuses = [
            1 => 'active',
            2 => 'pending',
            3 => 'inactive'
        ];
        return $statuses[$statusId] ?? 'unknown';
    }

    public function getStatusId($statusName)
    {
        $statuses = [
            'active' => 1,
            'pending' => 2,
            'inactive' => 3
        ];
        return $statuses[strtolower($statusName)] ?? null;
    }

    private function generateUuid()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}

