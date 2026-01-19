<?php
/**
 * API Routes Handler
 * Called from public/index.php when URI contains /api/
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\Api\ControllerDashboard;
use App\Controllers\Api\ControllerAttendance;
use App\Controllers\Api\ControllerEmployee;
use App\Controllers\Api\ControllerLeave;

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

$dashboardController = new ControllerDashboard();
$dashboardRoutes = [
    'GET' => [
        '/dashboard/summary'       => 'summary',
        '/dashboard/department'    => 'department',
        '/dashboard/recent-leaves' => 'recentLeaves',
    ],
];

if (isset($dashboardRoutes[$method][$route])) {
    $action = $dashboardRoutes[$method][$route];
    try {
        $dashboardController->$action();
    } catch (\Exception $e) {
        error_log("Dashboard $action error: " . $e->getMessage());
        sendJson([
            'success' => false,
            'message' => "Error in $action",
            'error' => $e->getMessage()
        ], 500);
    }
    exit;
}

/* ================= ATTENDANCE ROUTES ================= */

$attendanceController = new ControllerAttendance();
$attendanceRoutes = [
    'GET' => [
        '/attendance/today'  => 'today',
        '/attendance/show'   => 'show',
    ],
    'POST' => [
        '/attendance/checkin'  => 'checkIn',
        '/attendance/checkout' => 'checkOut',
    ],
];

if (isset($attendanceRoutes[$method][$route])) {
    $action = $attendanceRoutes[$method][$route];
    try {
        $attendanceController->$action();
    } catch (\Exception $e) {
        error_log("Attendance $action error: " . $e->getMessage());
        sendJson([
            'success' => false,
            'message' => "Error in $action",
            'error' => $e->getMessage()
        ], 500);
    }
    exit;
}


//* ========== EMPLOYEE ROUTES ========== */

    $employeeController = new ControllerEmployee();
    // List all employees
    if ($method === 'GET' && ($route === '/employees' || $route === '/employees/show')) {
        $employeeController->index();
        exit;
    }

    // Show single employee by ID
    if ($method === 'GET' && preg_match('#^/employees/(\d+)$#', $route, $matches)) {
        $employeeController->show((int)$matches[1]);
        exit;
    }

    // CREATE EMPLOYEE
    if ($method === 'POST' && $route === '/employees') {
        $employeeController->store();
        exit;
    }

    // Update employee
    if ($method === 'PUT' && preg_match('#^/employees/(\d+)$#', $route, $m)) {
        $employeeController->update((int)$m[1]);
    exit;
    }

    // Delete employee
    if ($method === 'POST' && $route === '/employees/delete') {
        $employeeController->destroy();
        exit;
    }


    // DELETE
    if ($method === 'DELETE' && preg_match('#^/employees/(\d+)$#', $route, $matches)) {
        $id = (int)$matches[1];
        $employeeController->delete($id); // or destroy($id)
        exit;
    }


/* ================= Leave ROUTES ================= */
    if (strpos($uri, '/api/leave') === 0) {

    $leave = new ControllerLeave();

    if ($uri === '/api/leave/list') {
        $leave->index();
        return;
    }

    if ($uri === '/api/leave/approve') {
        $leave->approve();
        return;
    }
}


/* ================= 404 FALLBACK ================= */

sendJson([
    'success' => false,
    'message' => 'API endpoint not found',
    'requested_route' => $route,
    'method' => $method
], 404);
