<?php

declare(strict_types=1);

// START SESSION - Required for permission checking
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Helpers/PermissionHelper.php';
use App\Core\Router;

// Create router instance
$router = new Router();

/* ================= DASHBOARD ROUTES ================= */
$router->get('/api/dashboard/summary',       'ControllerDashboard@summary');
$router->get('/api/dashboard/department',    'ControllerDashboard@department');
$router->get('/api/dashboard/recent-leaves', 'ControllerDashboard@recentLeaves');

/* ================= ATTENDANCE ROUTES ================= */
$router->get('/api/attendance/today',    'ControllerAttendance@today');
$router->get('/api/attendance/show',     'ControllerAttendance@show');
$router->post('/api/attendance/checkin', 'ControllerAttendance@checkIn');
$router->post('/api/attendance/checkout','ControllerAttendance@checkOut');

/* ================= EMPLOYEE ROUTES ================= */
$router->get('/api/employees',       'ControllerEmployee@index');
$router->get('/api/employees/show',  'ControllerEmployee@index');
$router->get('/api/employees/{id}',  'ControllerEmployee@show');
$router->post('/api/employees',      'ControllerEmployee@store');
$router->put('/api/employees/{id}',  'ControllerEmployee@update');
$router->delete('/api/employees/{id}','ControllerEmployee@delete');

/* ================= LEAVE ROUTES ================= */
$router->get('/api/leave/list',      'ControllerLeave@index');
$router->post('/api/leave/create',   'ControllerLeave@create');
$router->post('/api/leave/approve',  'ControllerLeave@approve');
$router->post('/api/leave/reject',   'ControllerLeave@reject');

/* ================= REPORT ROUTES ================= */
$router->get('/api/report/daily',         'ControllerReport@dailyList');
$router->get('/api/report/summary',       'ControllerReport@summary');
$router->get('/api/report/detailed',      'ControllerReport@detailedList');
$router->get('/api/report/top-employees', 'ControllerReport@topEmployees');

/* ================= USER ROUTES ================= */
$router->get('/api/users',          'ControllerUser@show');
$router->get('/api/users/show',     'ControllerUser@show');
$router->post('/api/users/create',  'ControllerUser@create');
$router->post('/api/users/update', 'ControllerUser@update');
$router->post('/api/users/delete',  'ControllerUser@delete');

/* ================= ROLE ROUTES ================= */
$router->get('/api/roles',              'ControllerRole@index');
$router->post('/api/roles',             'ControllerRole@store');
$router->get('/api/roles/stats',        'ControllerRole@stats');
$router->get('/api/roles/search',       'ControllerRole@search');
$router->get('/api/roles/{id}',         'ControllerRole@show');
$router->put('/api/roles/{id}',         'ControllerRole@update');
$router->delete('/api/roles/{id}',      'ControllerRole@destroy');
$router->patch('/api/roles/{id}/status','ControllerRole@updateStatus');

// Role → Permission relations (via RoleController)
$router->get('/api/roles/{id}/permissions',  'ControllerRole@rolePermissions');
$router->post('/api/roles/{id}/permissions', 'ControllerRole@updateRolePermissions');

// HTML view
$router->get('/roles',            'ControllerRole@show');
$router->post('/roles/create',    'ControllerRole@create');
$router->post('/roles/update',    'ControllerRole@update');
$router->put('/roles/{id}',       'ControllerRole@update');
$router->post('/roles/delete',    'ControllerRole@delete');
$router->delete('/roles/{id}',    'ControllerRole@delete');

/* ================= PERMISSION API ROUTES ================= */
// NOTE: specific paths MUST come before wildcard {id} paths

// List & grouped
$router->get('/api/permissions/list',    'ControllerPermission@index');
$router->get('/api/permissions/grouped', 'ControllerPermission@getByCategory');
$router->get('/api/permissions/categories', 'ControllerPermission@getCategories');

// Role-permission relations
$router->get('/api/permissions/role/{roleId}',           'ControllerPermission@getPermissionsByRole');
$router->post('/api/permissions/assign-to-role',         'ControllerPermission@assignToRole');
$router->post('/api/permissions/remove-from-role',       'ControllerPermission@removeFromRole');
$router->post('/api/permissions/assign-multiple-to-role','ControllerPermission@assignMultipleToRole');

// Permission check
$router->get('/api/permissions/check', 'ControllerPermission@checkUserPermission');

// CRUD (wildcard {id} last to avoid swallowing named paths)
$router->get('/api/permissions',         'ControllerPermission@index');
$router->post('/api/permissions',        'ControllerPermission@create');
$router->get('/api/permissions/{id}',    'ControllerPermission@getById');
$router->put('/api/permissions/{id}',    'ControllerPermission@update');
$router->delete('/api/permissions/{id}', 'ControllerPermission@delete');

/* ================= PERMISSION VIEW ROUTES ================= */
$router->get('/permissions',                              'ControllerPermission@show');
$router->get('/permissions/list',                         'ControllerPermission@index');
$router->get('/permissions/category',                     'ControllerPermission@getByCategory');
$router->get('/permissions/categories',                   'ControllerPermission@getCategories');
$router->get('/permissions/check',                        'ControllerPermission@checkUserPermission');
$router->get('/permissions/role/{roleId}',                'ControllerPermission@getPermissionsByRole');
$router->post('/permissions/create',                      'ControllerPermission@create');
$router->post('/permissions/assign-to-role',              'ControllerPermission@assignToRole');
$router->post('/permissions/remove-from-role',            'ControllerPermission@removeFromRole');
$router->post('/permissions/assign-multiple-to-role',     'ControllerPermission@assignMultipleToRole');
$router->get('/permissions/{id}',                         'ControllerPermission@getById');
$router->put('/permissions/{id}',                         'ControllerPermission@update');
$router->delete('/permissions/{id}',                      'ControllerPermission@delete');

/* ================= DISPATCH REQUEST ================= */
$router->dispatch();
