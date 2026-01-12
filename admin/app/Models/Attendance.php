<?php
declare(strict_types=1);

namespace App\Models;
use App\Core\Database;
use PDO;
use PDOException;

class Attendance
{
    private PDO $db;

    public function __construct()
    {
        try {
            $this->db = Database::getInstance()->getConnection();
            
            if (!$this->db) {
                throw new \Exception('Failed to establish database connection');
            }
        } catch (\Exception $e) {
            error_log("Attendance Model - Database Connection Error: " . $e->getMessage());
            throw $e;
        }
    }

    // Additional methods for Attendance model can be added here
}
