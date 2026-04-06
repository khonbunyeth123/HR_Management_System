<?php

namespace App\Controllers\Api;

use App\Models\User;
use App\Services\UserService;
use App\Helpers\Response;
use App\Helpers\PermissionHelper;

class ControllerUser
{
    private $userService;

    public function __construct()
    {
        $this->userService = new UserService();
    }

    /**
     * Get all users with pagination
     * GET /api/users/show
     */
    public function show()
    {
        try {
            if (!PermissionHelper::can('user', 'view')) {
                Response::error('Forbidden', 403);
            }
            $currentUserId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
            $currentRank = PermissionHelper::getRoleRank();
            // Get pagination parameters
            $page = isset($_GET['paging_options']['page']) ? (int)$_GET['paging_options']['page'] : 1;
            $per_page = isset($_GET['paging_options']['per_page']) ? (int)$_GET['paging_options']['per_page'] : 18;

            // Get filters
            $filters = isset($_GET['filters']) ? $_GET['filters'] : [];

            // Get sorting parameters
            $sorts = isset($_GET['sorts']) ? $_GET['sorts'] : [];

            // Call service
            $result = $this->userService->getAllUsers($page, $per_page, $filters, $sorts);

            if (!empty($result['data']['users'])) {
                $result['data']['users'] = array_values(array_filter($result['data']['users'], function ($u) use ($currentRank, $currentUserId) {
                    $targetRank = PermissionHelper::getRoleRank((int) ($u['role_id'] ?? 0));
                    if ($currentUserId && (int) $u['id'] === $currentUserId) {
                        return false; // hide self
                    }
                    return $targetRank <= $currentRank; // hide higher roles
                }));
            }

            Response::paginated(
                $result['data'],
                $result['pagination']['total'] ?? 0,
                $page,
                $per_page,
                'Users fetched successfully'
            );

        } catch (\Exception $e) {
            error_log("ControllerUser show error: " . $e->getMessage());
            Response::serverError('Error fetching users', ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Create a new user
     * POST /api/users/create
     */
    public function create()
    {
        try {
            if (!PermissionHelper::can('user', 'create')) {
                Response::error('Forbidden', 403);
            }
            // Get POST data
            $full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
            $username = isset($_POST['username']) ? trim($_POST['username']) : '';
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            $role_id = $_POST['role_id'] ?? ($_POST['role'] ?? '');
            $status_id = isset($_POST['status_id']) ? (int)$_POST['status_id'] : 1;

            // Get current user ID (from session or auth)
            $created_by = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

            // Validation
            $errors = [];
            
            if (empty($full_name)) {
                $errors['full_name'] = 'Full name is required';
            }

            if (empty($username)) {
                $errors['username'] = 'Username is required';
            }

            if (empty($email)) {
                $errors['email'] = 'Email is required';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Invalid email format';
            }

            if (empty($password) || strlen($password) < 6) {
                $errors['password'] = 'Password must be at least 6 characters';
            }

            if (empty($role_id)) {
                $errors['role_id'] = 'Role is required';
            }

            if (!empty($errors)) {
                Response::validationError($errors, 'Validation failed');
            }

            // Call service to create user
            $user = $this->userService->createUser(
                $full_name,
                $username,
                $email,
                $password,
                $role_id,
                $status_id,
                $created_by
            );

            Response::created($user, 'User created successfully');

        } catch (\Exception $e) {
            error_log("ControllerUser create error: " . $e->getMessage());
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * Get single user by ID
     * GET /api/users/{id}
     */
    public function getUserById(int $id)
    {
        try {
            if (!PermissionHelper::can('user', 'view')) {
                Response::error('Forbidden', 403);
            }
            if ($id <= 0) {
                Response::validationError(['id' => 'Invalid user ID']);
            }

            $user = $this->userService->getUserById($id);

            if (!$user) {
                Response::notFound('User not found');
            }

            Response::success($user, 'User fetched successfully');

        } catch (\Exception $e) {
            error_log("ControllerUser getUserById error: " . $e->getMessage());
            Response::serverError('Error fetching user', ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Update user
     * PUT /api/users/{id}
     */
    public function update(int $id)
    {
        try {
            if (!PermissionHelper::can('user', 'update')) {
                Response::error('Forbidden', 403);
            }
            $currentRank = PermissionHelper::getRoleRank();
            if ($id <= 0) {
                Response::validationError(['id' => 'Invalid user ID']);
            }
            $targetRoleId = $this->userService->getUserRoleId($id);
            $targetRank = PermissionHelper::getRoleRank($targetRoleId);
            if ($targetRank > $currentRank) {
                Response::error('Forbidden: cannot modify higher role', 403);
            }

            // Parse JSON or form data
            $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

            $full_name = isset($input['full_name']) ? trim($input['full_name']) : '';
            $email = isset($input['email']) ? trim($input['email']) : '';
            $role_id = $input['role_id'] ?? ($input['role'] ?? null);
            $status_id = isset($input['status_id']) ? (int)$input['status_id'] : null;

            // Get current user ID (from session or auth)
            $updated_by = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

            // Prevent non-admin from changing roles
            if ($role_id !== null) {
                $newRank = PermissionHelper::getRoleRank((int) $role_id);
                if ($newRank > $currentRank) {
                    Response::error('Forbidden: cannot assign higher role', 403);
                }
            }

            // Call service to update user
            $user = $this->userService->updateUser($id, $full_name, $email, $role_id, $status_id, $updated_by);

            Response::success($user, 'User updated successfully');

        } catch (\Exception $e) {
            error_log("ControllerUser update error: " . $e->getMessage());
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * Delete user
     * DELETE /api/users/{id}
     */
    public function delete(int $id)
    {
        try {
            if (!PermissionHelper::can('user', 'delete')) {
                Response::error('Forbidden', 403);
            }
            $currentRank = PermissionHelper::getRoleRank();
            if ($id <= 0) {
                Response::validationError(['id' => 'Invalid user ID']);
            }
            $targetRoleId = $this->userService->getUserRoleId($id);
            $targetRank = PermissionHelper::getRoleRank($targetRoleId);
            if ($targetRank > $currentRank) {
                Response::error('Forbidden: cannot delete higher role', 403);
            }

            // Get current user ID (from session or auth)
            $deleted_by = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

            $this->userService->deleteUser($id, $deleted_by);

            Response::success([], 'User deleted successfully');

        } catch (\Exception $e) {
            error_log("ControllerUser delete error: " . $e->getMessage());
            Response::error($e->getMessage(), 400);
        }
    }
}
