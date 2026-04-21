<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Auth
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findByUsername(string $username): array|false
    {
        $stmt = $this->db->prepare("
            SELECT 
                u.id,
                u.uuid,
                u.username,
                u.password,
                u.full_name,
                u.email,
                u.role_id,
                u.status_id,
                r.name AS role_name
            FROM tbl_users u
            LEFT JOIN tbl_roles r ON u.role_id = r.id
            WHERE u.username = :username
              AND u.deleted_at IS NULL
            LIMIT 1
        ");
        $stmt->execute([':username' => $username]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function updateLoginSession(int $id, string|null $token): bool
    {
        $stmt = $this->db->prepare("
            UPDATE tbl_users 
            SET login_session = :token, updated_at = NOW()
            WHERE id = :id
        ");
        return $stmt->execute([':token' => $token, ':id' => $id]);
    }
}