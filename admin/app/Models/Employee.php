<?php
declare(strict_types=1);

namespace App\Models;

use PDO;
use App\Core\Database;

class Employee
{
    private PDO $db;
    private string $table = 'tbl_employees';
    private ?array $tableColumns = null;

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
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($this->hasColumn('uuid')) {
            foreach ($rows as $index => $row) {
                $rows[$index] = $this->ensureUuid($row);
            }
        }

        return $rows;
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table}
            WHERE id = :id AND deleted_at IS NULL
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        if (!$row) {
            return null;
        }

        if ($this->hasColumn('uuid')) {
            $row = $this->ensureUuid($row);
        }

        return $row;
    }

    public function create(array $data): bool
    {
        $requiredKeys = [
            'username', 'first_name', 'last_name', 'full_name',
            'position', 'department', 'date_hired', 'status_id', 'created_at'
        ];

        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $data)) {
                throw new \InvalidArgumentException("Missing required field: $key");
            }
        }

        // Build params WITHOUT uuid — uuid is added below only if the column exists
        $params = [
            'photo'      => (!empty($data['photo']) && is_string($data['photo'])) ? $data['photo'] : null,
            'user_id'    => (array_key_exists('user_id', $data) && $data['user_id'] !== '' && $data['user_id'] !== null)
                                ? (int) $data['user_id'] : null,
            'username'   => (string) $data['username'],
            'first_name' => (string) $data['first_name'],
            'last_name'  => (string) $data['last_name'],
            'full_name'  => (string) $data['full_name'],
            'position'   => (string) $data['position'],
            'department' => (string) $data['department'],
            'date_hired' => (string) $data['date_hired'],
            'status_id'  => (int)    $data['status_id'],
            'created_at' => (string) $data['created_at'],
            'created_by' => (array_key_exists('created_by', $data) && $data['created_by'] !== '' && $data['created_by'] !== null)
                                ? (int) $data['created_by'] : null,
        ];

        // Only insert uuid if the column actually exists in the table
        if ($this->hasColumn('uuid')) {
            $params['uuid'] = !empty($data['uuid']) ? (string) $data['uuid'] : $this->generateUuid();
        }

        if ($this->hasColumn('employee_id') && array_key_exists('employee_id', $data)) {
            $employeeId = trim((string) $data['employee_id']);
            $params['employee_id'] = ($employeeId === '') ? null : $employeeId;
        }
        if ($this->hasColumn('gender') && array_key_exists('gender', $data)) {
            $params['gender'] = ($data['gender'] === '') ? null : (string) $data['gender'];
        }
        if ($this->hasColumn('email') && array_key_exists('email', $data)) {
            $params['email'] = ($data['email'] === '') ? null : (string) $data['email'];
        }
        if ($this->hasColumn('phone') && array_key_exists('phone', $data)) {
            $params['phone'] = ($data['phone'] === '') ? null : (string) $data['phone'];
        }
        if ($this->hasColumn('address') && array_key_exists('address', $data)) {
            $params['address'] = ($data['address'] === '') ? null : (string) $data['address'];
        }
        if ($this->hasColumn('dob') && array_key_exists('dob', $data)) {
            $params['dob'] = ($data['dob'] === '') ? null : (string) $data['dob'];
        }

        $autoGenerateEmployeeId = $this->hasColumn('employee_id')
            && (!array_key_exists('employee_id', $params) || $params['employee_id'] === null);

        if ($autoGenerateEmployeeId) {
            unset($params['employee_id']);
        }

        $insertColumns = array_keys($params);
        $placeholders  = array_map(static fn(string $col) => ':' . $col, $insertColumns);
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $insertColumns) . ") VALUES (" . implode(', ', $placeholders) . ")";

        $stmt = $this->db->prepare($sql);
        $this->db->beginTransaction();

        try {
            if (!$stmt->execute($params)) {
                throw new \RuntimeException('Failed to create employee.');
            }

            if ($autoGenerateEmployeeId) {
                $newId = (int) $this->db->lastInsertId();
                if ($newId <= 0) {
                    throw new \RuntimeException('Failed to resolve employee primary key.');
                }

                $generatedEmployeeId = $this->buildEmployeeId($newId);
                $updateStmt = $this->db->prepare(
                    "UPDATE {$this->table} SET employee_id = :employee_id WHERE id = :id"
                );
                if (!$updateStmt->execute(['employee_id' => $generatedEmployeeId, 'id' => $newId])) {
                    throw new \RuntimeException('Failed to generate employee ID.');
                }
            }

            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function update(int $id, array $data): bool
    {
        $allowedColumns = [
            'photo', 'user_id', 'username', 'first_name', 'last_name', 'full_name',
            'position', 'department', 'date_hired', 'status_id', 'updated_by'
        ];

        if ($this->hasColumn('gender'))  $allowedColumns[] = 'gender';
        if ($this->hasColumn('email'))   $allowedColumns[] = 'email';
        if ($this->hasColumn('phone'))   $allowedColumns[] = 'phone';
        if ($this->hasColumn('address')) $allowedColumns[] = 'address';
        if ($this->hasColumn('dob'))     $allowedColumns[] = 'dob';

        $set    = [];
        $params = ['id' => $id];

        foreach ($allowedColumns as $column) {
            if (!array_key_exists($column, $data)) {
                continue;
            }
            $set[] = "{$column} = :{$column}";
            if (in_array($column, ['user_id', 'status_id', 'updated_by'], true)) {
                $params[$column] = ($data[$column] === '' || $data[$column] === null)
                    ? null : (int) $data[$column];
            } else {
                $params[$column] = ($data[$column] === '') ? null : $data[$column];
            }
        }

        if (empty($set)) {
            return true;
        }

        $set[] = 'updated_at = NOW()';

        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET " . implode(', ', $set) . " WHERE id = :id AND deleted_at IS NULL"
        );

        return $stmt->execute($params);
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

    private function ensureUuid(array $row): array
    {
        $uuid = isset($row['uuid']) ? trim((string) $row['uuid']) : '';
        if ($uuid !== '') {
            return $row;
        }

        $generated = $this->generateUuid();

        if (isset($row['id'])) {
            $stmt = $this->db->prepare(
                "UPDATE {$this->table} SET uuid = :uuid WHERE id = :id AND (uuid IS NULL OR uuid = '')"
            );
            $stmt->execute(['uuid' => $generated, 'id' => (int) $row['id']]);
        }

        $row['uuid'] = $generated;
        return $row;
    }

    private function hasColumn(string $column): bool
    {
        if ($this->tableColumns === null) {
            $this->tableColumns = [];
            $stmt = $this->db->query("SHOW COLUMNS FROM {$this->table}");
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
                if (!empty($col['Field'])) {
                    $this->tableColumns[$col['Field']] = true;
                }
            }
        }

        return isset($this->tableColumns[$column]);
    }

    private function generateUuid(): string
    {
        $data    = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    private function buildEmployeeId(int $id): string
    {
        return 'EMP' . str_pad((string) $id, 5, '0', STR_PAD_LEFT);
    }
}