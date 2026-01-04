<?php
require_once __DIR__ . '/../vendor/autoload.php'; // ✅ only 2 levels up

use Dotenv\Dotenv;

// Load .env from /admin/
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// Get DB credentials
$sname = $_ENV['DB_HOST'];
$uname = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];
$db_name = $_ENV['DB_NAME'];

// Create connection
$cn = new mysqli($sname, $uname, $password, $db_name);

// Check connection
if ($cn->connect_error) {
  die("Connection failed: " . $cn->connect_error);
}
?>