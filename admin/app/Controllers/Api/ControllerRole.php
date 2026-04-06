<?php

namespace App\Controllers\Api;

use App\Services\RoleService;
use App\Helpers\Response;
use App\Helpers\PermissionHelper;
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
            if (!PermissionHelper::can('roles', 'view')) {
                return $this->response->error('Forbidden', 403);
            }

            $filters = [];
            if (!empty($_GET['status'])) {
                $filters['status'] = $_GET['status'];
            }

            $search = $_GET['search'] ?? '';

            $roles = $this->roleService->getAllRoles($filters);
            if (!empty($search)) {
                $roles = $this->roleService->searchRoles($search, $filters);
            }

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
            if (!PermissionHelper::can('roles', 'view')) {
                return $this->response->error('Forbidden', 403);
            }

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
            if (!PermissionHelper::isAdmin()) {
                return $this->response->error('Forbidden', 403);
            }

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
            if (!PermissionHelper::isAdmin()) {
                return $this->response->error('Forbidden', 403);
            }

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
            if (!PermissionHelper::isAdmin()) {
                return $this->response->error('Forbidden', 403);
            }

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
     * GET /api/roles/stats
     * Get role statistics
     */
    public function stats()
    {
        try {
            if (!PermissionHelper::can('roles', 'view')) {
                return $this->response->error('Forbidden', 403);
            }

            $stats = $this->roleService->getRoleStats();

            return $this->response->success([
                'data' => $stats
            ]);
        } catch (Exception $e) {
            return $this->response->error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * GET /api/roles/{id}/permissions
     */
    public function rolePermissions($id = null)
    {
        try {
            if (!PermissionHelper::can('roles', 'view')) {
                return $this->response->error('Forbidden', 403);
            }

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
     */
    public function updateRolePermissions($id = null)
    {
        try {
            if (!PermissionHelper::isAdmin()) {
                return $this->response->error('Forbidden', 403);
            }

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
     * PATCH /api/roles/{id}/status
     */
    public function updateStatus($id = null)
    {
        try {
            if (!PermissionHelper::isAdmin()) {
                return $this->response->error('Forbidden', 403);
            }

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

    /**
     * GET /api/roles/search
     */
    public function search()
    {
        try {
            if (!PermissionHelper::can('roles', 'view')) {
                return $this->response->error('Forbidden', 403);
            }

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

    private function getJsonInput()
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        return is_array($data) ? $data : [];
    }

    private function formatRoleForResponse($role)
    {
        return [
            'id'               => $role['id'],
            'uuid'             => $role['uuid'],
            'name'             => $role['name'],
            'slug'             => $role['slug'] ?? $this->generateSlug($role['name']),
            'description'      => $role['description'] ?? '',
            'status'           => $role['status'],
            'status_id'        => $role['status_id'],
            'user_count'       => $role['user_count'] ?? 0,
            'permission_count' => $role['permission_count'] ?? 0,
            'permissions'      => $role['permissions'] ?? [],
            'created_at'       => $role['created_at'],
            'updated_at'       => $role['updated_at'],
        ];
    }

    private function generateSlug($text)
    {
        $slug = strtolower(trim($text));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = preg_replace('/^-|-$/', '', $slug);
        return $slug;
    }
}

