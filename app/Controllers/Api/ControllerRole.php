<?php

namespace App\Controllers\Api;

use App\Services\RoleService;
use App\Helpers\Response;
use Exception;

class ControllerRole
{
    private $roleService;
    private $response;

    public function __construct()
    {
        $this->roleService = new RoleService();
        $this->response = new Response();
    }

    /**
     * GET /api/roles
     * Get all roles with optional filtering
     */
    public function index()
    {
        try {
            $filters = [];
            
            // Apply status filter if provided
            if (!empty($_GET['status'])) {
                $filters['status'] = $_GET['status'];
            }

            // Search query
            $search = $_GET['search'] ?? '';

            $roles = $this->roleService->getAllRoles($filters);

            // Apply search if provided
            if (!empty($search)) {
                $roles = $this->roleService->searchRoles($search, $filters);
            }

            // Enrich roles with permission count
            $roles = array_map(function ($role) {
                return $this->formatRoleForResponse($role);
            }, $roles);

            return $this->response->success([
                'data' => $roles,
                'count' => count($roles),
                'filters' => $filters
            ]);
        } catch (Exception $e) {
            return $this->response->error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * GET /api/roles/{id}
     * Get a single role with all permissions
     */
    public function show($id = null)
    {
        try {
            if (!$id) {
                $id = $_GET['id'] ?? null;
            }

            if (!$id) {
                return $this->response->error('Role ID is required', 400);
            }

            $role = $this->roleService->getRoleWithPermissions($id);
            
            return $this->response->success([
                'data' => $role
            ]);
        } catch (Exception $e) {
            return $this->response->error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * POST /api/roles
     * Create a new role
     */
    public function store()
    {
        try {
            // Check permission (implement based on your auth system)
            $this->checkPermission('roles.manage');

            $input = $this->getJsonInput();

            $result = $this->roleService->createRole([
                'name' => $input['name'] ?? '',
                'description' => $input['description'] ?? '',
                'permissions' => $input['permissions'] ?? []
            ]);

            return $this->response->success($result, 201);
        } catch (Exception $e) {
            return $this->response->error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * PUT /api/roles/{id}
     * Update a role
     */
    public function update($id = null)
    {
        try {
            $this->checkPermission('roles.manage');

            if (!$id) {
                $id = $_GET['id'] ?? null;
            }

            if (!$id) {
                return $this->response->error('Role ID is required', 400);
            }

            $input = $this->getJsonInput();

            $result = $this->roleService->updateRole($id, $input);

            return $this->response->success($result);
        } catch (Exception $e) {
            return $this->response->error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * DELETE /api/roles/{id}
     * Delete a role
     */
    public function destroy($id = null)
    {
        try {
            $this->checkPermission('roles.manage');

            if (!$id) {
                $id = $_GET['id'] ?? null;
            }

            if (!$id) {
                return $this->response->error('Role ID is required', 400);
            }

            $result = $this->roleService->deleteRole($id);

            return $this->response->success($result);
        } catch (Exception $e) {
            return $this->response->error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * POST /api/roles/{id}/accept
     * Accept/approve a pending role
     */
    public function accept($id = null)
    {
        try {
            $this->checkPermission('roles.manage');

            if (!$id) {
                $id = $_GET['id'] ?? null;
            }

            if (!$id) {
                return $this->response->error('Role ID is required', 400);
            }

            $result = $this->roleService->acceptRole($id);

            return $this->response->success($result);
        } catch (Exception $e) {
            return $this->response->error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * GET /api/roles/stats
     * Get role statistics
     */
    public function stats()
    {
        try {
            $stats = $this->roleService->getRoleStats();

            return $this->response->success([
                'data' => $stats
            ]);
        } catch (Exception $e) {
            return $this->response->error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * GET /api/permissions
     * Get all available permissions
     */
    public function permissions()
    {
        try {
            $permissions = $this->roleService->getAllPermissions();

            return $this->response->success([
                'data' => $permissions,
                'count' => count($permissions)
            ]);
        } catch (Exception $e) {
            return $this->response->error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * GET /api/permissions/grouped
     * Get permissions grouped by module
     */
    public function permissionsGrouped()
    {
        try {
            $permissions = $this->roleService->getPermissionsForDisplay();

            return $this->response->success([
                'data' => $permissions
            ]);
        } catch (Exception $e) {
            return $this->response->error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * GET /api/roles/{id}/permissions
     * Get permissions for a specific role
     */
    public function rolePermissions($id = null)
    {
        try {
            if (!$id) {
                $id = $_GET['id'] ?? null;
            }

            if (!$id) {
                return $this->response->error('Role ID is required', 400);
            }

            $role = $this->roleService->getRoleWithPermissions($id);

            return $this->response->success([
                'data' => [
                    'role_id' => $role['id'],
                    'role_name' => $role['name'],
                    'permissions' => $role['permissions'],
                    'permission_objects' => $role['permission_objects']
                ]
            ]);
        } catch (Exception $e) {
            return $this->response->error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * POST /api/roles/{id}/permissions
     * Update permissions for a role
     */
    public function updateRolePermissions($id = null)
    {
        try {
            $this->checkPermission('roles.manage');

            if (!$id) {
                $id = $_GET['id'] ?? null;
            }

            if (!$id) {
                return $this->response->error('Role ID is required', 400);
            }

            $input = $this->getJsonInput();
            $permissions = $input['permissions'] ?? [];

            if (!is_array($permissions)) {
                return $this->response->error('Permissions must be an array', 400);
            }

            $result = $this->roleService->updateRole($id, [
                'permissions' => $permissions
            ]);

            return $this->response->success(array_merge($result, [
                'role_id' => $id,
                'permissions' => $permissions
            ]));
        } catch (Exception $e) {
            return $this->response->error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * GET /api/roles/search
     * Search roles by name or description
     */
    public function search()
    {
        try {
            $query = $_GET['q'] ?? '';
            $status = $_GET['status'] ?? '';

            if (empty($query)) {
                return $this->response->error('Search query is required', 400);
            }

            $filters = [];
            if ($status) {
                $filters['status'] = $status;
            }

            $roles = $this->roleService->searchRoles($query, $filters);
            $roles = array_map(function ($role) {
                return $this->formatRoleForResponse($role);
            }, $roles);

            return $this->response->success([
                'data' => $roles,
                'count' => count($roles),
                'query' => $query
            ]);
        } catch (Exception $e) {
            return $this->response->error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * PATCH /api/roles/{id}/status
     * Update role status
     */
    public function updateStatus($id = null)
    {
        try {
            $this->checkPermission('roles.manage');

            if (!$id) {
                $id = $_GET['id'] ?? null;
            }

            if (!$id) {
                return $this->response->error('Role ID is required', 400);
            }

            $input = $this->getJsonInput();
            $status = $input['status'] ?? null;

            if (!$status) {
                return $this->response->error('Status is required', 400);
            }

            $result = $this->roleService->updateRole($id, [
                'status' => $status
            ]);

            return $this->response->success($result);
        } catch (Exception $e) {
            return $this->response->error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    // ─── PRIVATE HELPER METHODS ───────────────────────────────

    /**
     * Get JSON input from request body
     */
    private function getJsonInput()
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        return is_array($data) ? $data : [];
    }

    /**
     * Check if user has permission
     * Implement based on your authentication system
     */
    private function checkPermission($permission)
    {
        // TODO: Implement actual permission checking based on your auth system
        // For now, this is a placeholder
        // Example:
        // if (!$this->roleService->userHasPermission($_SESSION['user_id'], $permission)) {
        //     throw new Exception('Unauthorized', 403);
        // }
    }

    /**
     * Format role data for API response
     */
    private function formatRoleForResponse($role)
    {
        return [
            'id' => $role['id'],
            'uuid' => $role['uuid'],
            'name' => $role['name'],
            'slug' => $role['slug'] ?? $this->generateSlug($role['name']),
            'description' => $role['description'] ?? '',
            'status' => $role['status'],
            'status_id' => $role['status_id'],
            'user_count' => $role['user_count'] ?? 0,
            'permissions' => $role['permissions'] ?? [],
            'created_at' => $role['created_at'],
            'updated_at' => $role['updated_at']
        ];
    }

    /**
     * Generate slug from text
     */
    private function generateSlug($text)
    {
        $slug = strtolower(trim($text));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = preg_replace('/^-|-$/', '', $slug);
        return $slug;
    }
}