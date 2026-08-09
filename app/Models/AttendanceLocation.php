<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;
use PDOException;

class AttendanceLocation
{
    private PDO $db;
    private ?bool $tableReady = null;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function list(): array
    {
        $this->assertTableReady();
        $stmt = $this->db->query(
            "SELECT id, name, latitude, longitude, radius, status, created_at, updated_at
             FROM tbl_attendance_locations
             ORDER BY CASE WHEN status = 'active' THEN 0 ELSE 1 END, updated_at DESC, id DESC"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getCurrentLocation(): ?array
    {
        $this->assertTableReady();
        $stmt = $this->db->query(
            "SELECT id, name, latitude, longitude, radius, status, created_at, updated_at
             FROM tbl_attendance_locations
             WHERE status = 'active'
             ORDER BY updated_at DESC, id DESC
             LIMIT 1"
        );

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return $row;
        }

        $stmt = $this->db->query(
            "SELECT id, name, latitude, longitude, radius, status, created_at, updated_at
             FROM tbl_attendance_locations
             ORDER BY updated_at DESC, id DESC
             LIMIT 1"
        );

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getById(int $id): ?array
    {
        $this->assertTableReady();
        $stmt = $this->db->prepare(
            "SELECT id, name, latitude, longitude, radius, status, created_at, updated_at
             FROM tbl_attendance_locations
             WHERE id = :id
             LIMIT 1"
        );
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $this->assertTableReady();
        $sql = "INSERT INTO tbl_attendance_locations
                    (name, latitude, longitude, radius, status, created_at, updated_at)
                VALUES
                    (:name, :latitude, :longitude, :radius, :status, NOW(), NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':name' => $data['name'],
            ':latitude' => $data['latitude'],
            ':longitude' => $data['longitude'],
            ':radius' => $data['radius'],
            ':status' => $data['status'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $this->assertTableReady();
        $set = [];
        $params = [':id' => $id];

        foreach (['name', 'latitude', 'longitude', 'radius', 'status'] as $column) {
            if (!array_key_exists($column, $data)) {
                continue;
            }

            $set[] = $column . ' = :' . $column;
            $params[':' . $column] = $data[$column];
        }

        if (empty($set)) {
            return true;
        }

        $set[] = 'updated_at = NOW()';

        $stmt = $this->db->prepare(
            'UPDATE tbl_attendance_locations SET ' . implode(', ', $set) . ' WHERE id = :id'
        );

        return $stmt->execute($params);
    }

    public function activate(int $id): bool
    {
        $this->assertTableReady();
        $location = $this->getById($id);
        if (!$location) {
            return false;
        }

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare(
                "UPDATE tbl_attendance_locations
                 SET status = 'inactive', updated_at = NOW()
                 WHERE id <> :id"
            );
            $stmt->execute([':id' => $id]);

            $stmt = $this->db->prepare(
                "UPDATE tbl_attendance_locations
                 SET status = 'active', updated_at = NOW()
                 WHERE id = :id"
            );
            $stmt->execute([':id' => $id]);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function deactivate(int $id): bool
    {
        $this->assertTableReady();
        $stmt = $this->db->prepare(
            "UPDATE tbl_attendance_locations
             SET status = 'inactive', updated_at = NOW()
             WHERE id = :id"
        );

        return $stmt->execute([':id' => $id]);
    }

    private function assertTableReady(): void
    {
        if ($this->tableReady === true) {
            return;
        }

        if ($this->tableReady === false) {
            throw new \RuntimeException('Attendance locations table is not installed yet. Run the attendance locations migration first.');
        }

        $stmt = $this->db->prepare(
            "SELECT 1
             FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'tbl_attendance_locations'
             LIMIT 1"
        );
        $stmt->execute();
        $this->tableReady = (bool) $stmt->fetchColumn();

        if (!$this->tableReady) {
            throw new \RuntimeException('Attendance locations table is not installed yet. Run the attendance locations migration first.');
        }
    }
}
