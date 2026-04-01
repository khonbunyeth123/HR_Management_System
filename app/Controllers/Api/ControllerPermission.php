<?php

namespace App\Controllers\Api;

use App\Services\PermissionService;
use App\Helpers\Response;
use Exception;

class ControllerPermission
{
    protected $permissionService;

    public function __construct()
    {
        $this->permissionService = new PermissionService();
    }

    /**
     * Show permissions view page
     * GET /permissions
     */
    public function show()
    {
        try {
            $permissionsByModule = $this->permissionService->getPermissionsByCategory();
            $totalCount          = $this->permissionService->getPermissionCount();

            include __DIR__ . '/../../resources/views/permissions.php';
        } catch (Exception $e) {
            echo Response::error('Error loading permissions: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get all permissions as JSON
     * GET /permissions/list  |  GET /api/permissions/list
     */
    public function index()
    {
        try {
            $filters = [];

            if (!empty($_GET['search'])) {
                $filters['search'] = $_GET['search'];
            }
            if (!empty($_GET['module'])) {
                $filters['module'] = $_GET['module'];
            }
            if (isset($_GET['status_id'])) {
                $filters['status_id'] = $_GET['status_id'];
            }

            $permissions = $this->permissionService->getAllPermissions($filters);

            return Response::json([
                'success' => true,
                'message' => 'Success',
                'data'    => [
                    'data'  => $permissions,
                    'count' => count($permissions),
                ],
            ]);
        } catch (Exception $e) {
            return Response::error('Error fetching permissions: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get permissions grouped by module
     * GET /permissions/category  |  GET /api/permissions/grouped
     */
    public function getByCategory()
    {
        try {
            $permissionsByModule = $this->permissionService->getPermissionsByCategory();

            if (!empty($_GET['name'])) {
                $module      = $_GET['name'];
                $permissions = $permissionsByModule[$module] ?? [];

                return Response::json([
                    'success' => true,
                    'message' => 'Success',
                    'data'    => [
                        'module' => $module,
                        'data'   => $permissions,
                        'count'  => count($permissions),
                    ],
                ]);
            }

            return Response::json([
                'success' => true,
                'message' => 'Success',
                'data'    => $permissionsByModule,
            ]);
        } catch (Exception $e) {
            return Response::error('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get single permission by ID
     * GET /permissions/{id}  |  GET /api/permissions/{id}
     */
    public function getById($id)
    {
        try {
            $permission = $this->permissionService->getPermissionById($id);

            if (!$permission) {
                return Response::error('Permission not found', 404);
            }

            return Response::json([
                'success' => true,
                'message' => 'Success',
                'data'    => $permission,
            ]);
        } catch (Exception $e) {
            return Response::error('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get all distinct modules (categories)
     * GET /permissions/categories
     */
    public function getCategories()
    {
        try {
            $modules = $this->permissionService->getCategories();

            return Response::json([
                'success' => true,
                'message' => 'Success',
                'data'    => $modules,
            ]);
        } catch (Exception $e) {
            return Response::error('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create a new permission
     * POST /permissions/create  |  POST /api/permissions
     * Body: { "module": "users", "action": "view", "description": "..." }
     */
    public function create()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

            $validation = $this->permissionService->validatePermission($data);
            if (!$validation['valid']) {
                return Response::json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors'  => $validation['errors'],
                ], 400);
            }

            $permissionId = $this->permissionService->createPermission($data);

            if (!$permissionId) {
                return Response::error('Failed to create permission', 500);
            }

            return Response::json([
                'success' => true,
                'message' => '201',
                'data'    => [
                    'id'      => $permissionId,
                    'message' => 'Permission created successfully',
                ],
            ], 201);
        } catch (Exception $e) {
            return Response::error('Error: ' . $e->getMessage(), 400);
        }
    }

    /**
     * Update a permission
     * PUT /permissions/{id}  |  PUT /api/permissions/{id}
     * Body: { "module": "users", "action": "edit", "description": "...", "status_id": 1 }
     */
    public function update($id)
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

            $result = $this->permissionService->updatePermission($id, $data);

            if (!$result) {
                return Response::error('Failed to update permission', 500);
            }

            return Response::json([
                'success' => true,
                'message' => 'Success',
                'data'    => ['message' => 'Permission updated successfully'],
            ]);
        } catch (Exception $e) {
            return Response::error('Error: ' . $e->getMessage(), 400);
        }
    }

    /**
     * Soft-delete a permission
     * DELETE /permissions/{id}  |  DELETE /api/permissions/{id}
     */
    public function delete($id)
    {
        try {
            $result = $this->permissionService->deletePermission($id);

            if (!$result) {
                return Response::error('Failed to delete permission', 500);
            }

            return Response::json([
                'success' => true,
                'message' => 'Success',
                'data'    => ['message' => 'Permission deleted successfully'],
            ]);
        } catch (Exception $e) {
            return Response::error('Error: ' . $e->getMessage(), 400);
        }
    }

    /**
     * Get all permissions assigned to a role
     * GET /permissions/role/{roleId}  |  GET /api/permissions/role/{roleId}
     */
    public function getPermissionsByRole($roleId)
    {
        try {
            $permissions = $this->permissionService->getPermissionsByRole($roleId);

            return Response::json([
                'success' => true,
                'message' => 'Success',
                'data'    => [
                    'data'  => $permissions,
                    'count' => count($permissions),
                ],
            ]);
        } catch (Exception $e) {
            return Response::error('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Assign a single permission to a role
     * POST /permissions/assign-to-role
     * Body: { "role_id": 1, "permission_id": 5 }
     */
    public function assignToRole()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

            if (empty($data['role_id']) || empty($data['permission_id'])) {
                return Response::error('role_id and permission_id are required', 400);
            }

            $result = $this->permissionService->assignPermissionToRole(
                (int) $data['role_id'],
                (int) $data['permission_id']
            );

            if (!$result) {
                return Response::error('Failed to assign permission', 500);
            }

            return Response::json([
                'success' => true,
                'message' => 'Success',
                'data'    => ['message' => 'Permission assigned to role successfully'],
            ]);
        } catch (Exception $e) {
            return Response::error('Error: ' . $e->getMessage(), 400);
        }
    }

    /**
     * Remove a single permission from a role
     * POST /permissions/remove-from-role
     * Body: { "role_id": 1, "permission_id": 5 }
     */
    public function removeFromRole()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

            if (empty($data['role_id']) || empty($data['permission_id'])) {
                return Response::error('role_id and permission_id are required', 400);
            }

            $result = $this->permissionService->removePermissionFromRole(
                (int) $data['role_id'],
                (int) $data['permission_id']
            );

            if (!$result) {
                return Response::error('Failed to remove permission', 500);
            }

            return Response::json([
                'success' => true,
                'message' => 'Success',
                'data'    => ['message' => 'Permission removed from role successfully'],
            ]);
        } catch (Exception $e) {
            return Response::error('Error: ' . $e->getMessage(), 400);
        }
    }

    /**
     * Bulk replace all permissions for a role
     * POST /permissions/assign-multiple-to-role
     * Body: { "role_id": 1, "permission_ids": [1, 2, 3] }
     */
    public function assignMultipleToRole()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

            if (empty($data['role_id']) || !isset($data['permission_ids'])) {
                return Response::error('role_id and permission_ids are required', 400);
            }

            $result = $this->permissionService->assignMultiplePermissionsToRole(
                (int) $data['role_id'],
                (array) $data['permission_ids']
            );

            if (!$result) {
                return Response::error('Failed to assign permissions', 500);
            }

            return Response::json([
                'success' => true,
                'message' => 'Success',
                'data'    => ['message' => 'Permissions assigned to role successfully'],
            ]);
        } catch (Exception $e) {
            return Response::error('Error: ' . $e->getMessage(), 400);
        }
    }

    /**
     * Check if a user has a specific permission
     * GET /permissions/check?user_id=1&slug=users.view
     */
    public function checkUserPermission()
    {
        try {
            $userId = $_GET['user_id'] ?? null;
            $slug   = $_GET['slug']    ?? null;

            if (!$userId || !$slug) {
                return Response::error('user_id and slug are required', 400);
            }

            $hasPermission = $this->permissionService->userHasPermission($userId, $slug);

            return Response::json([
                'success'        => true,
                'message'        => 'Success',
                'has_permission' => $hasPermission,
            ]);
        } catch (Exception $e) {
            return Response::error('Error: ' . $e->getMessage(), 500);
        }
    }
}