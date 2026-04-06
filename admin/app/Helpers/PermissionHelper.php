<?php

namespace App\Helpers;

use App\Core\Database;
use PDO;

class PermissionHelper
{
    public static function can(string $module, string $action = 'view'): bool
    {
        $roleId = self::getRoleId();
        if (!$roleId) {
            return false;
        }

        $db = Database::getInstance()->getConnection();
        $query = "
            SELECT COUNT(*) AS count
            FROM tbl_role_permissions rp
            JOIN tbl_permissions p ON rp.permission_id = p.id
            WHERE rp.role_id = ?
              AND p.module = ?
              AND p.action = ?
              AND p.status_id = 1
              AND p.deleted_at IS NULL
        ";
        $stmt = $db->prepare($query);
        $stmt->execute([$roleId, $module, $action]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return isset($row['count']) && (int) $row['count'] > 0;
    }

    private static function getRoleId(): ?int
    {
        if (isset($_SESSION['role_id']) && is_numeric($_SESSION['role_id'])) {
            return (int) $_SESSION['role_id'];
        }

        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return null;
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT role_id FROM tbl_users WHERE id = ? AND deleted_at IS NULL LIMIT 1");
        $stmt->execute([(int) $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row && isset($row['role_id']) ? (int) $row['role_id'] : null;
    }

    public static function isAdmin(): bool
    {
        $roleId = self::getRoleId();
        if (!$roleId) return false;

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT name FROM tbl_roles WHERE id = ? LIMIT 1");
        $stmt->execute([$roleId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return isset($row['name']) && strtolower($row['name']) === 'admin';
    }

    public static function getRoleRank(?int $roleId = null): int
    {
        $roleId = $roleId ?? self::getRoleId();
        if (!$roleId) return 0;

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT name FROM tbl_roles WHERE id = ? LIMIT 1");
        $stmt->execute([$roleId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $name = strtolower($row['name'] ?? '');

        // Higher number = higher privilege
        return match ($name) {
            'admin' => 3,
            'manager' => 2,
            'employee' => 1,
            default => 0,
        };
    }
}
