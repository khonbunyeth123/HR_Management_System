<?php
declare(strict_types=1);

namespace App\Models;

use PDO;
use App\Core\Database;

class Employee
{
    private PDO $db;
    private string $table = 'tbl_employees';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll(): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table}
            WHERE deleted_at IS NULL
            ORDER BY id DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table}
            WHERE id = :id AND deleted_at IS NULL
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

   public function create(array $data): bool
    {
        $sql = "
            INSERT INTO {$this->table} 
            (uuid, user_id, username, first_name, last_name, full_name,
             position, department, date_hired, status_id, created_at, created_by)
            VALUES
            (:uuid, :user_id, :username, :first_name, :last_name, :full_name,
             :position, :department, :date_hired, :status_id, :created_at, :created_by)
        ";

        $stmt = $this->db->prepare($sql);

        // Make sure all keys exist
        $requiredKeys = [
            'uuid', 'user_id', 'username', 'first_name', 'last_name', 'full_name',
            'position', 'department', 'date_hired', 'status_id', 'created_at', 'created_by'
        ];

        foreach ($requiredKeys as $key) {
            if (!isset($data[$key])) {
                throw new \InvalidArgumentException("Missing required field: $key");
            }
        }

        return $stmt->execute($data);
    }

    public function update(int $id, array $data): bool
    {
        $set = [];
        foreach ($data as $col => $val) {
            $set[] = "$col = ?";
        }
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET " . implode(',', $set) . " WHERE id = ?"
        );
        return $stmt->execute([...array_values($data), $id]);
    }

    public function Delete(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE {$this->table}
            SET deleted_at = NOW(), deleted_by = ?
            WHERE id = ?
        ");
        return $stmt->execute([$userId, $id]);
    }
}
