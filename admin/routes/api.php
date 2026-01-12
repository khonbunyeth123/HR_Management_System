<?php
/**
 * API Routes Handler
 * Called from public/index.php when URI contains /api/
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\Api\ControllerDashboard;

// Get the request URI and clean it
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Remove /api prefix for routing
$route = str_replace('/api', '', $uri);

// Remove query string
$route = explode('?', $route)[0];

// Normalize route
$route = rtrim($route, '/') ?: '/';

error_log("API Route: $method $route");

/**
 * Route matcher function
 */
function matchRoute(string $method, string $pattern, string $requestMethod, string $requestRoute): bool
{
    return $requestMethod === $method && $requestRoute === $pattern;
}

/**
 * Send JSON response
 */
function sendJson(array $data, int $statusCode = 200): void
{
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

/* ================= DASHBOARD ROUTES ================= */

if (matchRoute('GET', '/dashboard/summary', $method, $route)) {
    try {
        (new ControllerDashboard())->summary();
    } catch (\Exception $e) {
        error_log("Dashboard summary error: " . $e->getMessage());
        sendJson([
            'success' => false,
            'message' => 'Error loading statistics',
            'error' => $e->getMessage()
        ], 500);
    }
    exit;
}

if (matchRoute('GET', '/dashboard/department', $method, $route)) {
    try {
        (new ControllerDashboard())->department();
    } catch (\Exception $e) {
        error_log("Dashboard department error: " . $e->getMessage());
        sendJson([
            'success' => false,
            'message' => 'Error loading departments',
            'error' => $e->getMessage()
        ], 500);
    }
    exit;
}

if (matchRoute('GET', '/dashboard/recent-leaves', $method, $route)) {
    try {
        (new ControllerDashboard())->recentLeaves();
    } catch (\Exception $e) {
        error_log("Dashboard recent leaves error: " . $e->getMessage());
        sendJson([
            'success' => false,
            'message' => 'Error loading leave requests',
            'error' => $e->getMessage()
        ], 500);
    }
    exit;
}

/* ================= 404 FALLBACK ================= */

sendJson([
    'success' => false,
    'message' => 'API endpoint not found',
    'requested_route' => $route,
    'method' => $method
], 404);