<?php

namespace App\Services;

use App\Models\Role;
use Exception;

class RoleService
{
    private $roleModel;

    public function __construct()
    {
        $this->roleModel = new Role();
    }

    public function getAllRoles($filters = [])
    {
        $roles = $this->roleModel->getAllRoles();
        $roles = array_map(function ($role) {
            $role['status'] = $this->roleModel->getStatusName($role['status_id']);
            return $role;
        }, $roles);

        if (!empty($filters['status'])) {
            $roles = array_filter($roles, function ($role) use ($filters) {
                return $role['status'] === $filters['status'];
            });
        }

        return $roles;
    }

    public function getRoleWithPermissions($roleId)
    {
        $role = $this->roleModel->getRoleById($roleId);
        if (!$role) {
            throw new Exception('Role not found', 404);
        }

        $permissions = $this->roleModel->getRolePermissions($roleId);
        $permissionSlugs = array_map(function ($p) {
            return $p['permission_slug'];
        }, $permissions);

        $role['status'] = $this->roleModel->getStatusName($role['status_id']);
        $role['permissions'] = $permissionSlugs;
        $role['permission_objects'] = $permissions;
        return $role;
    }

    public function createRole($data)
    {
        if (empty($data['name'])) {
            throw new Exception('Role name is required', 422);
        }
        if ($this->roleModel->roleExists($data['name'])) {
            throw new Exception('Role name already exists', 422);
        }

        try {
            $roleId = $this->roleModel->createRole([
                'name' => $data['name'],
                'description' => $data['description'] ?? '',
                'status_id' => 2,
            ]);

            if (!empty($data['permissions']) && is_array($data['permissions'])) {
                $this->assignPermissionsToRole($roleId, $data['permissions']);
            }

            return [
                'id' => $roleId,
                'message' => 'Role created successfully and is pending approval'
            ];
        } catch (Exception $e) {
            throw new Exception('Failed to create role: ' . $e->getMessage(), 500);
        }
    }

    public function updateRole($roleId, $data)
    {
        $role = $this->roleModel->getRoleById($roleId);
        if (!$role) {
            throw new Exception('Role not found', 404);
        }

        $updateData = [];
        if (isset($data['name'])) {
            if (empty($data['name'])) {
                throw new Exception('Role name cannot be empty', 422);
            }
            if ($this->roleModel->roleExists($data['name'], $roleId)) {
                throw new Exception('Role name already exists', 422);
            }
            $updateData['name'] = $data['name'];
        }
        if (isset($data['description'])) {
            $updateData['description'] = $data['description'];
        }
        if (isset($data['status'])) {
            $statusId = $this->roleModel->getStatusId($data['status']);
            if (!$statusId) {
                throw new Exception('Invalid status', 422);
            }
            $updateData['status_id'] = $statusId;
        }
        if (isset($data['status_id'])) {
            $updateData['status_id'] = $data['status_id'];
        }

        try {
            $this->roleModel->updateRole($roleId, $updateData);
            if (isset($data['permissions']) && is_array($data['permissions'])) {
                $this->assignPermissionsToRole($roleId, $data['permissions']);
            }

            return ['message' => 'Role updated successfully'];
        } catch (Exception $e) {
            throw new Exception('Failed to update role: ' . $e->getMessage(), 500);
        }
    }

    public function deleteRole($roleId)
    {
        $role = $this->roleModel->getRoleById($roleId);
        if (!$role) {
            throw new Exception('Role not found', 404);
        }
        if ($role['user_count'] > 0) {
            throw new Exception('Cannot delete role with assigned users. Please reassign users first.', 422);
        }

        try {
            $this->roleModel->deleteRole($roleId);
            return ['message' => 'Role deleted successfully'];
        } catch (Exception $e) {
            throw new Exception('Failed to delete role: ' . $e->getMessage(), 500);
        }
    }

    private function assignPermissionsToRole($roleId, $permissionSlugs)
    {
        $allPermissions = $this->roleModel->getAllPermissions();
        $permissionMap = [];
        foreach ($allPermissions as $perm) {
            $permissionMap[$perm['permission_slug']] = $perm['id'];
        }

        $permissionIds = [];
        foreach ($permissionSlugs as $slug) {
            if (!isset($permissionMap[$slug])) {
                throw new Exception("Invalid permission: {$slug}", 422);
            }
            $permissionIds[] = $permissionMap[$slug];
        }

        $this->roleModel->syncPermissions($roleId, $permissionIds);
    }

    public function getAllPermissions()
    {
        $permissions = $this->roleModel->getAllPermissions();
        return array_map(function ($p) {
            return [
                'value' => $p['permission_slug'],
                'label' => $p['permission_slug'],
                'module' => $p['module'],
                'action' => $p['action'],
            ];
        }, $permissions);
    }

    public function getPermissionsForDisplay()
    {
        $permissions = $this->getAllPermissions();
        $grouped = [];
        foreach ($permissions as $perm) {
            $grouped[$perm['module']][] = $perm;
        }
        return $grouped;
    }

    public function getRolesByStatus($status)
    {
        $statusId = $this->roleModel->getStatusId($status);
        if (!$statusId) return [];

        $roles = $this->roleModel->getAllRoles();
        return array_filter($roles, function ($role) use ($statusId) {
            return $role['status_id'] === $statusId;
        });
    }

    public function searchRoles($query, $filters = [])
    {
        $roles = $this->getAllRoles($filters);
        if (empty($query)) return $roles;

        return array_filter($roles, function ($role) use ($query) {
            return strpos(strtolower($role['name']), $query) !== false ||
                   strpos(strtolower($role['description'] ?? ''), $query) !== false ||
                   strpos(strtolower($role['uuid']), $query) !== false;
        });
    }

    public function getRoleStats()
    {
        $roles = $this->roleModel->getAllRoles();
        $stats = [
            'total_roles' => count($roles),
            'active_roles' => 0,
            'pending_roles' => 0,
            'inactive_roles' => 0,
            'total_users' => 0
        ];

        foreach ($roles as $role) {
            if ($role['status_id'] === 1) $stats['active_roles']++;
            elseif ($role['status_id'] === 2) $stats['pending_roles']++;
            elseif ($role['status_id'] === 3) $stats['inactive_roles']++;
            $stats['total_users'] += $role['user_count'] ?? 0;
        }
        return $stats;
    }
}

