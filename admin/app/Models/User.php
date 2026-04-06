<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class User
{
    protected $table = 'tbl_users';
    protected $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Get all users
     */
    public function all()
    {
        $query = "SELECT * FROM {$this->table} WHERE deleted_at IS NULL";
        return $this->query($query);
    }

    /**
     * Get user by ID
     */
    public function find(int $id)
    {
        $query = "SELECT * FROM {$this->table} WHERE id = $id AND deleted_at IS NULL LIMIT 1";
        $result = $this->query($query);
        return !empty($result) ? $result[0] : null;
    }

    /**
     * Get user by UUID
     */
    public function findByUUID(string $uuid)
    {
        $query = "SELECT * FROM {$this->table} WHERE uuid = ? AND deleted_at IS NULL LIMIT 1";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$uuid]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return !empty($result) ? $result[0] : null;
    }

    /**
     * Get user by username
     */
    public function findByUsername(string $username)
    {
        $query = "SELECT * FROM {$this->table} WHERE username = ? AND deleted_at IS NULL LIMIT 1";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$username]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return !empty($result) ? $result[0] : null;
    }

    /**
     * Get user by email
     */
    public function findByEmail(string $email)
    {
        $query = "SELECT * FROM {$this->table} WHERE email = ? AND deleted_at IS NULL LIMIT 1";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$email]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return !empty($result) ? $result[0] : null;
    }

    /**
     * Check if username exists
     */
    public function usernameExists(string $username, int $excludeId = null)
    {
        $query = "SELECT id FROM {$this->table} WHERE username = ? AND deleted_at IS NULL";
        if ($excludeId) {
            $query .= " AND id != ?";
            $params = [$username, $excludeId];
        } else {
            $params = [$username];
        }
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return !empty($result);
    }

    /**
     * Check if email exists
     */
    public function emailExists(string $email, int $excludeId = null)
    {
        $query = "SELECT id FROM {$this->table} WHERE email = ? AND deleted_at IS NULL";
        if ($excludeId) {
            $query .= " AND id != ?";
            $params = [$email, $excludeId];
        } else {
            $params = [$email];
        }
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return !empty($result);
    }

    /**
     * Create user
     */
    public function create(array $data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $query = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(array_values($data));
        
        return $this->pdo->lastInsertId();
    }

    /**
     * Update user
     */
    public function update(int $id, array $data)
    {
        $columns = array_keys($data);
        $setClause = implode(', ', array_map(fn($col) => "$col = ?", $columns));
        $query = "UPDATE {$this->table} SET $setClause WHERE id = ? AND deleted_at IS NULL";
        
        $values = array_values($data);
        $values[] = $id;
        
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute($values);
    }

    /**
     * Delete user (soft delete)
     */
    public function delete(int $id, $deletedBy = null)
    {
        if ($deletedBy) {
            $query = "UPDATE {$this->table} SET deleted_at = NOW(), deleted_by = ? WHERE id = ?";
            $stmt = $this->pdo->prepare($query);
            return $stmt->execute([$deletedBy, $id]);
        } else {
            $query = "UPDATE {$this->table} SET deleted_at = NOW() WHERE id = ?";
            $stmt = $this->pdo->prepare($query);
            return $stmt->execute([$id]);
        }
    }

    /**
     * Permanently delete user (hard delete)
     */
    public function forceDelete(int $id)
    {
        $query = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute([$id]);
    }

    /**
     * Get paginated users
     */
    public function paginate(int $page = 1, int $perPage = 18, array $filters = [])
    {
        if ($page < 1) $page = 1;
        if ($perPage < 1) $perPage = 18;

        $offset = ($page - 1) * $perPage;

        // Build where clause
        $where = "WHERE deleted_at IS NULL";
        $params = [];
        
        if (!empty($filters)) {
            if (isset($filters['status_id'])) {
                $where .= " AND status_id = ?";
                $params[] = $filters['status_id'];
            }
            if (isset($filters['role_id'])) {
                $where .= " AND role_id = ?";
                $params[] = $filters['role_id'];
            } elseif (isset($filters['role'])) {
                $roleId = $this->getRoleIdByName($filters['role']);
                if ($roleId !== null) {
                    $where .= " AND role_id = ?";
                    $params[] = $roleId;
                }
            }
            if (isset($filters['search'])) {
                $where .= " AND (full_name LIKE ? OR username LIKE ? OR email LIKE ?)";
                $search = "%{$filters['search']}%";
                $params[] = $search;
                $params[] = $search;
                $params[] = $search;
            }
        }

        // Get total count
        $countQuery = "SELECT COUNT(*) as total FROM {$this->table} $where";
        $stmt = $this->pdo->prepare($countQuery);
        $stmt->execute($params);
        $countResult = $stmt->fetch(PDO::FETCH_ASSOC);
        $total = (int)($countResult['total'] ?? 0);

        // Get paginated data
        $query = "SELECT * FROM {$this->table} $where ORDER BY created_at DESC LIMIT $offset, $perPage";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'data' => $data,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage)
            ]
        ];
    }

    /**
     * Get users by role
     */
    public function getByRole(string $role)
    {
        $query = "SELECT u.* 
                  FROM {$this->table} u
                  LEFT JOIN tbl_roles r ON u.role_id = r.id
                  WHERE LOWER(r.name) = LOWER(?)
                    AND u.deleted_at IS NULL
                  ORDER BY u.created_at DESC";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$role]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getRoleIdByName(string $role): ?int
    {
        $stmt = $this->pdo->prepare("SELECT id FROM tbl_roles WHERE LOWER(name) = LOWER(?) LIMIT 1");
        $stmt->execute([$role]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row && isset($row['id']) ? (int) $row['id'] : null;
    }

    /**
     * Get active users
     */
    public function getActive()
    {
        $query = "SELECT * FROM {$this->table} WHERE status_id = 1 AND deleted_at IS NULL ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get inactive users
     */
    public function getInactive()
    {
        $query = "SELECT * FROM {$this->table} WHERE status_id = 0 AND deleted_at IS NULL ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get deleted users
     */
    public function getDeleted()
    {
        $query = "SELECT * FROM {$this->table} WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Count total users
     */
    public function count()
    {
        $query = "SELECT COUNT(*) as total FROM {$this->table} WHERE deleted_at IS NULL";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['total'] ?? 0);
    }

    /**
     * Count active users
     */
    public function countActive()
    {
        $query = "SELECT COUNT(*) as total FROM {$this->table} WHERE status_id = 1 AND deleted_at IS NULL";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['total'] ?? 0);
    }

    /**
     * Count inactive users
     */
    public function countInactive()
    {
        $query = "SELECT COUNT(*) as total FROM {$this->table} WHERE status_id = 0 AND deleted_at IS NULL";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['total'] ?? 0);
    }

    /**
     * Search users
     */
    public function search(string $searchQuery)
    {
        $query = "SELECT * FROM {$this->table} WHERE deleted_at IS NULL AND (full_name LIKE ? OR username LIKE ? OR email LIKE ?) ORDER BY created_at DESC";
        $search = "%$searchQuery%";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$search, $search, $search]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get user with login session
     */
    public function getWithLoginSession(string $sessionId)
    {
        $query = "SELECT * FROM {$this->table} WHERE login_session = ? AND deleted_at IS NULL LIMIT 1";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$sessionId]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return !empty($result) ? $result[0] : null;
    }

    /**
     * Update login session
     */
    public function updateLoginSession(int $id, string $sessionId)
    {
        $query = "UPDATE {$this->table} SET login_session = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute([$sessionId, $id]);
    }

    /**
     * Clear login session
     */
    public function clearLoginSession(int $id)
    {
        $query = "UPDATE {$this->table} SET login_session = NULL WHERE id = ?";
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute([$id]);
    }

    /**
     * Get table name
     */
    public function getTableName()
    {
        return $this->table;
    }

    /**
     * Execute raw query
     */
    protected function query(string $sql)
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
