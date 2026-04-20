<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class User
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    // Get user by ID
    public function getById(int $id)
    {
        $stmt = $this->pdo->prepare("\n            SELECT u.id, u.uuid, u.username, u.full_name, u.email, u.role_id, u.status_id, u.created_at, r.name as role_name
            FROM tbl_users u
            LEFT JOIN tbl_roles r ON u.role_id = r.id
            WHERE u.id = ? AND u.deleted_at IS NULL
            LIMIT 1
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get all users with optional filters, pagination
    public function getAll(int $offset = 0, int $limit = 18, array $filters = [], array $sorts = [])
    {
        $where = "WHERE u.deleted_at IS NULL";
        $params = [];

        if (!empty($filters['search'])) {
            $where .= " AND (u.username LIKE ? OR u.full_name LIKE ? OR u.email LIKE ?)";
            $search = "%{$filters['search']}%";
            $params = array_merge($params, [$search, $search, $search]);
        }

        $orderBy = "ORDER BY u.created_at DESC";
        if (!empty($sorts['property']) && in_array($sorts['property'], ['id','username','full_name','email','role_id','status_id','created_at'])) {
            $dir = strtoupper($sorts['direction'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';
            $orderBy = "ORDER BY u.{$sorts['property']} $dir";
        }

        // Total count
        $countStmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM tbl_users u $where");
        $countStmt->execute($params);
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        // Data
        $stmt = $this->pdo->prepare("\n            SELECT u.id, u.uuid, u.username, u.full_name, u.email, u.role_id, u.status_id, u.created_at, r.name as role_name
            FROM tbl_users u
            LEFT JOIN tbl_roles r ON u.role_id = r.id
            $where
            $orderBy
            LIMIT ?, ?
        ");
        $stmt->execute(array_merge($params, [$offset, $limit]));
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ['data' => $users, 'total' => $total];
    }

    // Create user
    public function create(array $data)
    {
        try {
            $uuid = $this->generateUuid();

            error_log("User Model - Creating user with username: " . $data['username']);

            $stmt = $this->pdo->prepare("\n                INSERT INTO tbl_users (uuid, username, full_name, email, password, role_id, status_id, created_at, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?)
            ");

            $result = $stmt->execute([
                $uuid,
                $data['username'],
                $data['full_name'],
                $data['email'],
                password_hash($data['password'], PASSWORD_BCRYPT),
                $data['role_id'],
                $data['status_id'] ?? 1,
                $data['created_by'] ?? null
            ]);

            if (!$result) {
                error_log("User Model - Insert failed: " . json_encode($stmt->errorInfo()));
                throw new \Exception("Failed to insert user: " . $stmt->errorInfo()[2]);
            }

            error_log("User Model - User created with ID: " . $this->pdo->lastInsertId());

            return $this->getById((int) $this->pdo->lastInsertId());

        } catch (\Exception $e) {
            error_log("User Model - Create exception: " . $e->getMessage());
            throw $e;
        }
    }

    // Update user
    public function update(int $id, array $data)
    {
        $updates = [];
        $params = [];

        foreach (['full_name','email','role_id','status_id'] as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (!empty($data['password'])) {
            $updates[] = "password = ?";
            $params[] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        if (empty($updates)) return $this->getById($id);

        $updates[] = "updated_at = NOW()";
        if (isset($data['updated_by'])) {
            $updates[] = "updated_by = ?";
            $params[] = $data['updated_by'];
        }

        $params[] = $id;
        $sql = "UPDATE tbl_users SET " . implode(', ', $updates) . " WHERE id = ? AND deleted_at IS NULL";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $this->getById($id);
    }

    // Soft delete user
    public function delete(int $id, $deleted_by = null)
    {
        $sql = "UPDATE tbl_users SET deleted_at = NOW(), deleted_by = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$deleted_by, $id]);
    }

    // Get permissions for a user
    public function getPermissions(int $userId)
    {
        $stmt = $this->pdo->prepare("\n            SELECT p.module, p.action
            FROM tbl_role_permissions rp
            JOIN tbl_permissions p ON rp.permission_id = p.id
            JOIN tbl_users u ON u.role_id = rp.role_id
            WHERE u.id = ? AND p.status_id = 1
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
