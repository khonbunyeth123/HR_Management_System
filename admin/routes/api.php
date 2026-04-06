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

/* ================= ROLE ROUTES ================= */

$router->get('/roles',               'ControllerRole@index');
$router->post('/roles',              'ControllerRole@store');
$router->get('/roles/stats',         'ControllerRole@stats');
$router->get('/roles/search',        'ControllerRole@search');
$router->get('/roles/{id}',          'ControllerRole@show');
$router->put('/roles/{id}',          'ControllerRole@update');
$router->delete('/roles/{id}',       'ControllerRole@destroy');
$router->patch('/roles/{id}/status', 'ControllerRole@updateStatus');
$router->get('/roles/{id}/permissions',  'ControllerRole@rolePermissions');
$router->post('/roles/{id}/permissions', 'ControllerRole@updateRolePermissions');

/* ================= PERMISSION ROUTES ================= */

$router->get('/permissions',            'ControllerPermission@index');
$router->post('/permissions',           'ControllerPermission@create');
$router->get('/permissions/list',       'ControllerPermission@index');
$router->get('/permissions/grouped',    'ControllerPermission@getByCategory');
$router->get('/permissions/categories', 'ControllerPermission@getCategories');
$router->get('/permissions/role/{roleId}',                'ControllerPermission@getPermissionsByRole');
$router->post('/permissions/assign-to-role',              'ControllerPermission@assignToRole');
$router->post('/permissions/remove-from-role',            'ControllerPermission@removeFromRole');
$router->post('/permissions/assign-multiple-to-role',     'ControllerPermission@assignMultipleToRole');
$router->get('/permissions/{id}',       'ControllerPermission@getById');
$router->put('/permissions/{id}',       'ControllerPermission@update');
$router->delete('/permissions/{id}',    'ControllerPermission@delete');

/* ================= DISPATCH REQUEST ================= */

// This will execute the matched route or return 404
$router->dispatch();
