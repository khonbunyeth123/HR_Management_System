<?php

namespace App\Controllers\Api;

use App\Services\PermissionService;
use App\Helpers\Response;
use App\Helpers\PermissionHelper;
use Exception;

class ControllerPermission
{
    protected $permissionService;

    public function __construct()
    {
        $this->permissionService = new PermissionService();
    }

    public function index()
    {
        try {
            if (!PermissionHelper::can('permissions', 'view')) {
                return Response::error('Forbidden', 403);
            }

            $filters = [];
            if (!empty($_GET['search'])) $filters['search'] = $_GET['search'];
            if (!empty($_GET['module'])) $filters['module'] = $_GET['module'];
            if (isset($_GET['status_id'])) $filters['status_id'] = $_GET['status_id'];

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

    public function getByCategory()
    {
        try {
            if (!PermissionHelper::can('permissions', 'view')) {
                return Response::error('Forbidden', 403);
            }

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

    public function getById($id)
    {
        try {
            if (!PermissionHelper::can('permissions', 'view')) {
                return Response::error('Forbidden', 403);
            }

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

    public function getCategories()
    {
        try {
            if (!PermissionHelper::can('permissions', 'view')) {
                return Response::error('Forbidden', 403);
            }

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

    public function create()
    {
        try {
            if (!PermissionHelper::isAdmin()) {
                return Response::error('Forbidden', 403);
            }

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

    public function update($id)
    {
        try {
            if (!PermissionHelper::isAdmin()) {
                return Response::error('Forbidden', 403);
            }

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

    public function delete($id)
    {
        try {
            if (!PermissionHelper::isAdmin()) {
                return Response::error('Forbidden', 403);
            }

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

    public function getPermissionsByRole($roleId)
    {
        try {
            if (!PermissionHelper::can('permissions', 'view')) {
                return Response::error('Forbidden', 403);
            }

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

    public function assignToRole()
    {
        try {
            if (!PermissionHelper::isAdmin()) {
                return Response::error('Forbidden', 403);
            }

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

    public function removeFromRole()
    {
        try {
            if (!PermissionHelper::isAdmin()) {
                return Response::error('Forbidden', 403);
            }

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

    public function assignMultipleToRole()
    {
        try {
            if (!PermissionHelper::isAdmin()) {
                return Response::error('Forbidden', 403);
            }

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
}

