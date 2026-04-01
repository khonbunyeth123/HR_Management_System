<?php
/**
 * API Routes - Using Router Class
 * 
 * This file registers all API endpoints using the clean Router class
 * Much cleaner and more maintainable than the old approach
 * 
 * Called from public/index.php when URI contains /api/
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;

// Create router instance
$router = new Router();

/* ================= DASHBOARD ROUTES ================= */

$router->get('/dashboard/summary', 'ControllerDashboard@summary');
$router->get('/dashboard/department', 'ControllerDashboard@department');
$router->get('/dashboard/recent-leaves', 'ControllerDashboard@recentLeaves');

/* ================= ATTENDANCE ROUTES ================= */

$router->get('/attendance/today', 'ControllerAttendance@today');
$router->get('/attendance/show', 'ControllerAttendance@show');
$router->post('/attendance/checkin', 'ControllerAttendance@checkIn');
$router->post('/attendance/checkout', 'ControllerAttendance@checkOut');

/* ================= EMPLOYEE ROUTES ================= */

$router->get('/employees', 'ControllerEmployee@index');
$router->get('/employees/show', 'ControllerEmployee@index');
$router->get('/employees/{id}', 'ControllerEmployee@show');
$router->post('/employees', 'ControllerEmployee@store');
$router->put('/employees/{id}', 'ControllerEmployee@update');
$router->delete('/employees/{id}', 'ControllerEmployee@delete');
$router->post('/employees/delete', 'ControllerEmployee@destroy');

/* ================= LEAVE ROUTES ================= */

$router->get('/leave/list', 'ControllerLeave@index');
$router->post('/leave/create', 'ControllerLeave@create');
$router->post('/leave/approve', 'ControllerLeave@approve');
$router->post('/leave/reject', 'ControllerLeave@reject');

/* ================= REPORT ROUTES ================= */

$router->get('/report/daily', 'ControllerReport@dailyList');
$router->get('/report/summary', 'ControllerReport@summary');
$router->get('/report/detailed', 'ControllerReport@detailedList');
$router->get('/report/top-employees', 'ControllerReport@topEmployees');

/* ================= USER ROUTES ================= */

$router->get('/users', 'ControllerUser@show');
$router->get('/users/show', 'ControllerUser@show');
$router->post('/users/create', 'ControllerUser@create');
$router->get('/users/{id}', 'ControllerUser@getUserById');
$router->put('/users/{id}', 'ControllerUser@update');
$router->delete('/users/{id}', 'ControllerUser@delete');

/* ================= DISPATCH REQUEST ================= */

// This will execute the matched route or return 404
$router->dispatch();
