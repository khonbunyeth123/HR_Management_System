<?php

namespace App\Controllers\Api;

use App\Models\User;
use App\Services\UserService;

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
            // Get pagination parameters
            $page = isset($_GET['paging_options']['page']) ? (int)$_GET['paging_options']['page'] : 1;
            $per_page = isset($_GET['paging_options']['per_page']) ? (int)$_GET['paging_options']['per_page'] : 18;

            // Get filters
            $filters = isset($_GET['filters']) ? $_GET['filters'] : [];

            // Get sorting parameters
            $sorts = isset($_GET['sorts']) ? $_GET['sorts'] : [];

            // Call service
            $result = $this->userService->getAllUsers($page, $per_page, $filters, $sorts);

            sendJson([
                'success' => true,
                'message' => 'Users fetched successfully',
                'data' => $result['data'],
                'pagination' => $result['pagination']
            ], 200);

        } catch (\Exception $e) {
            error_log("ControllerUser show error: " . $e->getMessage());
            sendJson([
                'success' => false,
                'message' => 'Error fetching users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new user
     * POST /api/users/create
     */
    public function create()
    {
        try {
            // Get POST data
            $full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
            $username = isset($_POST['username']) ? trim($_POST['username']) : '';
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            $role = isset($_POST['role']) ? trim($_POST['role']) : '';
            $status_id = isset($_POST['status_id']) ? (int)$_POST['status_id'] : 1;

            // Get current user ID (from session or auth)
            $created_by = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

            // Validation
            if (empty($full_name)) {
                throw new \Exception('Full name is required');
            }

            if (empty($username)) {
                throw new \Exception('Username is required');
            }

            if (empty($email)) {
                throw new \Exception('Email is required');
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new \Exception('Invalid email format');
            }

            if (empty($password) || strlen($password) < 6) {
                throw new \Exception('Password must be at least 6 characters');
            }

            if (empty($role)) {
                throw new \Exception('Role is required');
            }

            // Call service to create user
            $user = $this->userService->createUser(
                $full_name,
                $username,
                $email,
                $password,
                $role,
                $status_id,
                $created_by
            );

            sendJson([
                'success' => true,
                'message' => 'User created successfully',
                'data' => $user
            ], 201);

        } catch (\Exception $e) {
            error_log("ControllerUser create error: " . $e->getMessage());
            sendJson([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get single user by ID
     * GET /api/users/{id}
     */
    public function getUserById(int $id)
    {
        try {
            $user = $this->userService->getUserById($id);

            if (!$user) {
                throw new \Exception('User not found');
            }

            sendJson([
                'success' => true,
                'message' => 'User fetched successfully',
                'data' => $user
            ], 200);

        } catch (\Exception $e) {
            error_log("ControllerUser getUserById error: " . $e->getMessage());
            sendJson([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update user
     * PUT /api/users/{id}
     */
    public function update(int $id)
    {
        try {
            // Parse JSON or form data
            $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

            $full_name = isset($input['full_name']) ? trim($input['full_name']) : '';
            $email = isset($input['email']) ? trim($input['email']) : '';
            $role = isset($input['role']) ? trim($input['role']) : '';
            $status_id = isset($input['status_id']) ? (int)$input['status_id'] : null;

            // Get current user ID (from session or auth)
            $updated_by = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

            // Call service to update user
            $user = $this->userService->updateUser($id, $full_name, $email, $role, $status_id, $updated_by);

            sendJson([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => $user
            ], 200);

        } catch (\Exception $e) {
            error_log("ControllerUser update error: " . $e->getMessage());
            sendJson([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Delete user
     * DELETE /api/users/{id}
     */
    public function delete(int $id)
    {
        try {
            // Get current user ID (from session or auth)
            $deleted_by = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

            $this->userService->deleteUser($id, $deleted_by);

            sendJson([
                'success' => true,
                'message' => 'User deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            error_log("ControllerUser delete error: " . $e->getMessage());
            sendJson([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}