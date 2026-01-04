<?php
header('Content-Type: application/json');

// ✅ Add autoloader
require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\Api\DashboardController;

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

/**
 * Simple route matcher
 */
function api(string $method, string $path): bool
{
    global $uri;
    return $_SERVER['REQUEST_METHOD'] === $method && $uri === "/api{$path}";
}

/* ================= DASHBOARD ================= */

if (api('GET', '/dashboard/summary')) {
    (new DashboardController())->summary();
    exit;
}

if (api('GET', '/dashboard/department')) {
    (new DashboardController())->department();
    exit;
}

if (api('GET', '/dashboard/recent-leaves')) {
    (new DashboardController())->recentLeaves();
    exit;
}

/* ================= FALLBACK ================= */

http_response_code(404);
echo json_encode([
    'success' => false,
    'message' => 'API endpoint not found'
]);
exit;