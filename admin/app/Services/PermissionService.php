<?php

namespace App\Services;

use App\Core\Database;
use Exception;
use PDO;

class PermissionService
{
    protected $table = 'tbl_permissions';
    protected $rolePermissionTable = 'tbl_role_permissions';

    public function getAllPermissions($filters = [])
    {
        try {
            $db = Database::getInstance()->getConnection();
            $query = "SELECT * FROM {$this->table} WHERE deleted_at IS NULL";
            $params = [];

            if (!empty($filters['search'])) {
                $query .= " AND (module LIKE :search OR action LIKE :search OR description LIKE :search)";
                $params['search'] = '%' . $filters['search'] . '%';
            }
            if (!empty($filters['module'])) {
                $query .= " AND module = :module";
                $params['module'] = $filters['module'];
            }
            if (isset($filters['status_id'])) {
                $query .= " AND status_id = :status_id";
                $params['status_id'] = $filters['status_id'];
            }

            $query .= " ORDER BY module ASC, action ASC";

            $stmt = $db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new Exception("Error fetching permissions: " . $e->getMessage());
        }
    }

    public function getPermissionById($id)
    {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT * FROM {$this->table} WHERE id = :id AND deleted_at IS NULL");
            $stmt->execute(['id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new Exception("Error fetching permission: " . $e->getMessage());
        }
    }

    public function getPermissionByModuleAction($module, $action)
    {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT * FROM {$this->table} WHERE module = :module AND action = :action AND deleted_at IS NULL");
            $stmt->execute(['module' => $module, 'action' => $action]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new Exception("Error fetching permission: " . $e->getMessage());
        }
    }

    public function getPermissionsByCategory()
    {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT * FROM {$this->table} WHERE deleted_at IS NULL ORDER BY module ASC, action ASC");
            $stmt->execute();
            $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $grouped = [];
            foreach ($permissions as $permission) {
                $mod = $permission['module'];
                if (!isset($grouped[$mod])) $grouped[$mod] = [];
                $grouped[$mod][] = $permission;
            }

            return $grouped;
        } catch (\PDOException $e) {
            throw new Exception("Error grouping permissions: " . $e->getMessage());
        }
    }

    public function getPermissionsByRole($roleId)
    {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                SELECT p.*
                FROM {$this->table} p
                INNER JOIN {$this->rolePermissionTable} rp ON p.id = rp.permission_id
                WHERE rp.role_id = :role_id AND p.deleted_at IS NULL
            ");
            $stmt->execute(['role_id' => $roleId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new Exception("Error fetching role permissions: " . $e->getMessage());
        }
    }

    public function createPermission($data)
    {
        $db = Database::getInstance()->getConnection();
        $module = trim($data['module'] ?? '');
        $action = trim($data['action'] ?? '');
        $description = $data['description'] ?? null;
        $status_id = $data['status_id'] ?? 1;

        if ($this->getPermissionByModuleAction($module, $action)) {
            throw new Exception("Permission '{$module}.{$action}' already exists");
        }

        $stmt = $db->prepare("
            INSERT INTO {$this->table} (uuid, module, action, description, status_id, created_at, created_by)
            VALUES (:uuid, :module, :action, :description, :status_id, :created_at, :created_by)
        ");

        $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );

        $created_by = $_SESSION['user_id'] ?? null;
        $created_at = date('Y-m-d H:i:s');

        $stmt->execute([
            'uuid' => $uuid,
            'module' => $module,
            'action' => $action,
            'description' => $description,
            'status_id' => $status_id,
            'created_at' => $created_at,
            'created_by' => $created_by
        ]);

        return $db->lastInsertId();
    }

    public function updatePermission($id, $data)
    {
        $db = Database::getInstance()->getConnection();
        if (!$this->getPermissionById($id)) {
            throw new Exception("Permission not found");
        }

        $module = trim($data['module'] ?? '');
        $action = trim($data['action'] ?? '');
        $description = $data['description'] ?? null;
        $status_id = $data['status_id'] ?? 1;

        $existing = $this->getPermissionByModuleAction($module, $action);
        if ($existing && (int)$existing['id'] !== (int)$id) {
            throw new Exception("Permission '{$module}.{$action}' already exists");
        }

        $stmt = $db->prepare("
            UPDATE {$this->table}
            SET module = :module, action = :action, description = :description, status_id = :status_id,
                updated_at = :updated_at, updated_by = :updated_by
            WHERE id = :id
        ");

        $updated_by = $_SESSION['user_id'] ?? null;
        $updated_at = date('Y-m-d H:i:s');

        return $stmt->execute([
            'module' => $module,
            'action' => $action,
            'description' => $description,
            'status_id' => $status_id,
            'updated_at' => $updated_at,
            'updated_by' => $updated_by,
            'id' => $id
        ]);
    }

    public function deletePermission($id)
    {
        $db = Database::getInstance()->getConnection();
        if (!$this->getPermissionById($id)) {
            throw new Exception("Permission not found");
        }

        $stmt = $db->prepare("SELECT COUNT(*) as count FROM {$this->rolePermissionTable} WHERE permission_id = :id");
        $stmt->execute(['id' => $id]);
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
        if ($count > 0) {
            throw new Exception("Cannot delete permission that is assigned to roles");
        }

        $stmt = $db->prepare("
            UPDATE {$this->table} 
            SET deleted_at = :deleted_at, deleted_by = :deleted_by
            WHERE id = :id
        ");
        $deleted_by = $_SESSION['user_id'] ?? null;
        $deleted_at = date('Y-m-d H:i:s');

        return $stmt->execute([
            'deleted_at' => $deleted_at,
            'deleted_by' => $deleted_by,
            'id' => $id
        ]);
    }

    public function assignPermissionToRole($roleId, $permissionId)
    {
        $db = Database::getInstance()->getConnection();
        if (!$this->getPermissionById($permissionId)) {
            throw new Exception("Permission not found");
        }

        $stmt = $db->prepare("SELECT id FROM {$this->rolePermissionTable} WHERE role_id = :role_id AND permission_id = :permission_id");
        $stmt->execute(['role_id' => $roleId, 'permission_id' => $permissionId]);
        if ($stmt->fetch()) {
            throw new Exception("Permission already assigned to this role");
        }

        $stmt = $db->prepare("INSERT INTO {$this->rolePermissionTable} (role_id, permission_id) VALUES (:role_id, :permission_id)");
        return $stmt->execute(['role_id' => $roleId, 'permission_id' => $permissionId]);
    }

    public function removePermissionFromRole($roleId, $permissionId)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM {$this->rolePermissionTable} WHERE role_id = :role_id AND permission_id = :permission_id");
        return $stmt->execute(['role_id' => $roleId, 'permission_id' => $permissionId]);
    }

    public function assignMultiplePermissionsToRole($roleId, $permissionIds)
    {
        $db = Database::getInstance()->getConnection();
        $db->beginTransaction();

        $stmt = $db->prepare("DELETE FROM {$this->rolePermissionTable} WHERE role_id = :role_id");
        $stmt->execute(['role_id' => $roleId]);

        if (!empty($permissionIds)) {
            $stmt = $db->prepare("INSERT INTO {$this->rolePermissionTable} (role_id, permission_id) VALUES (:role_id, :permission_id)");
            foreach ($permissionIds as $permissionId) {
                if (!$stmt->execute(['role_id' => $roleId, 'permission_id' => (int) $permissionId])) {
                    throw new Exception("Failed to assign permission ID: " . $permissionId);
                }
            }
        }

        $db->commit();
        return true;
    }

    public function getPermissionCount()
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT COUNT(*) as count FROM {$this->table} WHERE deleted_at IS NULL");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'] ?? 0;
    }

    public function getCategories()
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT DISTINCT module FROM {$this->table} WHERE deleted_at IS NULL ORDER BY module ASC");
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'module');
    }

    public function validatePermission($data)
    {
        $errors = [];
        if (empty($data['module'])) $errors['module'] = 'Module is required';
        if (empty($data['action'])) $errors['action'] = 'Action is required';

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}

