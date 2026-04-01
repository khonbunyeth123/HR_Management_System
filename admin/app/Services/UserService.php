<?php

namespace App\Services;

use App\Models\User;
use App\Core\Database;
use PDO;

class UserService
{
    private $userModel;
    private $pdo;

    public function __construct()
    {
        $this->userModel = new User();
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Get all users with pagination, filters, and sorting
     */
    public function getAllUsers(int $page = 1, int $per_page = 18, array $filters = [], array $sorts = [])
    {
        // Validate pagination
        if ($page < 1) $page = 1;
        if ($per_page < 1) $per_page = 18;

        $offset = ($page - 1) * $per_page;

        // Build filters
        $where_clause = "WHERE deleted_at IS NULL";
        $params = [];
        
        if (!empty($filters)) {
            foreach ($filters as $filter) {
                if (isset($filter['property']) && $filter['property'] === 'status_id') {
                    $status_id = (int)$filter['value'];
                    $where_clause .= " AND status_id = ?";
                    $params[] = $status_id;
                }
            }
        }

        // Build sorting
        $sort_property = 'created_at';
        $sort_direction = 'DESC';

        if (!empty($sorts)) {
            foreach ($sorts as $sort) {
                if (isset($sort['property'])) {
                    $allowed_sorts = ['id', 'uuid', 'username', 'full_name', 'email', 'role', 'status_id', 'created_at', 'updated_at'];
                    if (in_array($sort['property'], $allowed_sorts)) {
                        $sort_property = $sort['property'];
                    }
                }
                if (isset($sort['direction']) && in_array(strtoupper($sort['direction']), ['ASC', 'DESC'])) {
                    $sort_direction = strtoupper($sort['direction']);
                }
            }
        }

        // Get total count
        $countQuery = "SELECT COUNT(*) as total FROM tbl_users $where_clause";
        $stmt = $this->pdo->prepare($countQuery);
        $stmt->execute($params);
        $countResult = $stmt->fetch(PDO::FETCH_ASSOC);
        $total = (int)($countResult['total'] ?? 0);

        // Get paginated data
        $query = "SELECT id, uuid, username, full_name, email, role, status_id, created_at, updated_at 
                  FROM tbl_users 
                  $where_clause 
                  ORDER BY $sort_property $sort_direction 
                  LIMIT $offset, $per_page";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'data' => ['users' => $users],
            'pagination' => [
                'page' => $page,
                'per_page' => $per_page,
                'total' => $total,
                'total_pages' => ceil($total / $per_page)
            ]
        ];
    }

    /**
     * Create a new user
     */
    public function createUser(string $full_name, string $username, string $email, string $password, string $role, int $status_id = 1, $created_by = null)
    {
        // Check if username already exists
        $checkUsername = "SELECT id FROM tbl_users WHERE username = ? AND deleted_at IS NULL";
        $stmt = $this->pdo->prepare($checkUsername);
        $stmt->execute([$username]);
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            throw new \Exception('Username already exists');
        }

        // Check if email already exists
        $checkEmail = "SELECT id FROM tbl_users WHERE email = ? AND deleted_at IS NULL";
        $stmt = $this->pdo->prepare($checkEmail);
        $stmt->execute([$email]);
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            throw new \Exception('Email already exists');
        }

        // Generate UUID
        $uuid = $this->generateUUID();

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Insert user
        $query = "INSERT INTO tbl_users (uuid, username, password, full_name, email, role, status_id, created_at, created_by) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?)";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$uuid, $username, $hashed_password, $full_name, $email, $role, $status_id, $created_by]);

        return [
            'uuid' => $uuid,
            'username' => $username,
            'full_name' => $full_name,
            'email' => $email,
            'role' => $role,
            'status_id' => $status_id
        ];
    }

    /**
     * Get single user by ID
     */
    public function getUserById(int $id)
    {
        $query = "SELECT id, uuid, username, full_name, email, role, status_id, created_at, updated_at 
                  FROM tbl_users WHERE id = ? AND deleted_at IS NULL LIMIT 1";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    /**
     * Update user
     */
    public function updateUser(int $id, string $full_name = '', string $email = '', string $role = '', $status_id = null, $updated_by = null)
    {
        $updates = [];
        $params = [];

        if (!empty($full_name)) {
            $updates[] = "full_name = ?";
            $params[] = $full_name;
        }

        if (!empty($email)) {
            // Check if email already exists for another user
            $checkEmail = "SELECT id FROM tbl_users WHERE email = ? AND id != ? AND deleted_at IS NULL";
            $stmt = $this->pdo->prepare($checkEmail);
            $stmt->execute([$email, $id]);
            if ($stmt->fetch(PDO::FETCH_ASSOC)) {
                throw new \Exception('Email already exists');
            }
            $updates[] = "email = ?";
            $params[] = $email;
        }

        if (!empty($role)) {
            $updates[] = "role = ?";
            $params[] = $role;
        }

        if ($status_id !== null) {
            $updates[] = "status_id = ?";
            $params[] = $status_id;
        }

        if (empty($updates)) {
            throw new \Exception('No fields to update');
        }

        $updates[] = "updated_at = NOW()";
        if ($updated_by) {
            $updates[] = "updated_by = ?";
            $params[] = $updated_by;
        }

        $updateStr = implode(', ', $updates);
        $params[] = $id;

        $query = "UPDATE tbl_users SET $updateStr WHERE id = ? AND deleted_at IS NULL";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);

        return $this->getUserById($id);
    }

    /**
     * Soft delete user
     */
    public function deleteUser(int $id, $deleted_by = null)
    {
        if ($deleted_by) {
            $query = "UPDATE tbl_users SET deleted_at = NOW(), deleted_by = ? WHERE id = ?";
            $stmt = $this->pdo->prepare($query);
            return $stmt->execute([$deleted_by, $id]);
        } else {
            $query = "UPDATE tbl_users SET deleted_at = NOW() WHERE id = ?";
            $stmt = $this->pdo->prepare($query);
            return $stmt->execute([$id]);
        }
    }

    /**
     * Generate UUID (hex format like in migration)
     */
    private function generateUUID()
    {
        return bin2hex(random_bytes(18));
    }
}