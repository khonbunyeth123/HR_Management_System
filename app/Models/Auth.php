<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Auth
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findAdminByIdentifier(string $identifier): array|false
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
            WHERE (u.username = :username_identifier OR u.email = :email_identifier)
              AND u.status_id = 1
              AND u.deleted_at IS NULL
            LIMIT 1
        ");
        $stmt->execute([
            ':username_identifier' => $identifier,
            ':email_identifier' => $identifier,
        ]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function findEmployeeByIdentifier(string $identifier): array|false
    {
        $stmt = $this->db->prepare("
            SELECT
                e.id,
                e.uuid,
                e.username,
                e.password,
                e.full_name,
                e.email,
                e.status_id
            FROM tbl_employees e
            WHERE (e.username = :username_identifier OR e.email = :email_identifier)
              AND e.deleted_at IS NULL
              AND e.status_id = 1
            LIMIT 1
        ");
        $stmt->execute([
            ':username_identifier' => $identifier,
            ':email_identifier' => $identifier,
        ]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function createAccessToken(string $tokenableType, int $tokenableId, string $token, ?string $expiresAt = null): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO tbl_access_tokens (token, tokenable_type, tokenable_id, expires_at, created_at)
            VALUES (:token, :tokenable_type, :tokenable_id, :expires_at, NOW())
        ");
        return $stmt->execute([
            ':token' => $token,
            ':tokenable_type' => $tokenableType,
            ':tokenable_id' => $tokenableId,
            ':expires_at' => $expiresAt,
        ]);
    }

    public function findAccessToken(string $token): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM tbl_access_tokens
            WHERE token = :token
              AND revoked_at IS NULL
              AND (expires_at IS NULL OR expires_at > NOW())
            LIMIT 1
        ");
        $stmt->bindValue(':token', $token);
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function touchAccessToken(int $tokenId): void
    {
        $stmt = $this->db->prepare("
            UPDATE tbl_access_tokens
            SET last_used_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([':id' => $tokenId]);
    }

    public function revokeAccessToken(string $token): bool
    {
        $stmt = $this->db->prepare("
            UPDATE tbl_access_tokens
            SET revoked_at = NOW()
            WHERE token = :token AND revoked_at IS NULL
        ");
        return $stmt->execute([':token' => $token]);
    }

    public function getAdminById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                u.id,
                u.uuid,
                u.username,
                u.full_name,
                u.email,
                u.role_id,
                u.status_id,
                r.name AS role_name
            FROM tbl_users u
            LEFT JOIN tbl_roles r ON r.id = u.role_id
            WHERE u.id = :id
              AND u.status_id = 1
              AND u.deleted_at IS NULL
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getEmployeeById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                e.id,
                e.uuid,
                e.username,
                e.full_name,
                e.email,
                e.status_id
            FROM tbl_employees e
            WHERE e.id = :id
              AND e.status_id = 1
              AND e.deleted_at IS NULL
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
