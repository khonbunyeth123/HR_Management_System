<?php
/**
 * database/cn.php
 * 
 * Full PDO database connection using .env
 * Drop-in replacement for old MySQLi cn.php
 */

require_once __DIR__ . '/../vendor/autoload.php'; // Load composer packages
use Dotenv\Dotenv;
use PDO;
use PDOException;

// Load .env from project root (adjust path if needed)
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// Get database credentials from .env
$host     = $_ENV['DB_HOST'] ?? 'localhost';
$port     = $_ENV['DB_PORT'] ?? 3306;
$dbname   = $_ENV['DB_NAME'] ?? 'doorstep';
$username = $_ENV['DB_USER'] ?? 'root';
$password = $_ENV['DB_PASS'] ?? '';

try {
    // Create PDO connection
    $cn = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );
    
    // Optional: Uncomment to test connection
    // echo "✅ Database connected successfully";

} catch (PDOException $e) {
    // Stop execution if connection fails
    die("❌ Database connection failed: " . $e->getMessage());
}
