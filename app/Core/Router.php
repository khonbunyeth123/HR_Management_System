<?php

namespace App\Core;

class Router
{
    private array $routes = [];
    private array $parameters = [];
    private string $method;
    private string $route;
    private string $controllerNamespace = 'App\\Controllers\\Api\\';

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->route = $this->cleanUri();
        error_log("Router initialized: {$this->method} {$this->route}");
    }

    private function cleanUri(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        $basePath = '/project_doorstep/my_project_3/admin';

        if (strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }

        $uri = explode('?', $uri)[0];
        $uri = rtrim($uri, '/') ?: '/';

        return $uri;
    }

    public function get(string $path, string $handler): self
    {
        return $this->register('GET', $path, $handler);
    }

    public function post(string $path, string $handler): self
    {
        return $this->register('POST', $path, $handler);
    }

    public function put(string $path, string $handler): self
    {
        return $this->register('PUT', $path, $handler);
    }

    public function delete(string $path, string $handler): self
    {
        return $this->register('DELETE', $path, $handler);
    }

    public function patch(string $path, string $handler): self
    {
        return $this->register('PATCH', $path, $handler);
    }

    public function any(string $path, string $handler): self
    {
        foreach (['GET', 'POST', 'PUT', 'DELETE', 'PATCH'] as $method) {
            $this->register($method, $path, $handler);
        }
        return $this;
    }

    private function register(string $method, string $path, string $handler): self
    {
        if (!isset($this->routes[$method])) {
            $this->routes[$method] = [];
        }

        $path = rtrim($path, '/') ?: '/';
        [$controller, $action] = explode('@', $handler);

        $this->routes[$method][$path] = [
            'controller' => $controller,
            'action' => $action,
        ];

        return $this;
    }

    public function resource(string $name, string $controller): self
    {
        $path = "/$name";

        $this->get($path, "$controller@index");
        $this->get("$path/create", "$controller@create");
        $this->post($path, "$controller@store");
        $this->get("$path/{id}", "$controller@show");
        $this->get("$path/{id}/edit", "$controller@edit");
        $this->put("$path/{id}", "$controller@update");
        $this->delete("$path/{id}", "$controller@destroy");

        return $this;
    }

    public function dispatch(): void
    {
        if (isset($this->routes[$this->method][$this->route])) {
            $this->executeRoute($this->routes[$this->method][$this->route]);
            exit;
        }

        foreach ($this->routes[$this->method] ?? [] as $pattern => $handler) {
            if ($this->matchPattern($pattern, $this->route, $this->parameters)) {
                $this->executeRoute($handler);
                exit;
            }
        }

        $this->notFound();
    }

    private function matchPattern(string $pattern, string $route, array &$parameters): bool
    {
        $regex = preg_quote($pattern, '#');
        $regex = preg_replace('#\\\{(\w+)\\\}#', '([^/]+)', $regex);
        $regex = "#^$regex$#";

        if (!preg_match($regex, $route, $matches)) {
            return false;
        }

        preg_match_all('#\{(\w+)\}#', $pattern, $paramNames);

        for ($i = 0; $i < count($paramNames[1]); $i++) {
            $parameters[$paramNames[1][$i]] = $matches[$i + 1];
        }

        return true;
    }

    private function executeRoute(array $handler): void
    {
        try {
            $this->authorizeRoute($handler);

            $controllerClass = $this->controllerNamespace . $handler['controller'];
            $actionMethod = $handler['action'];

            if (!class_exists($controllerClass)) {
                throw new \RuntimeException("Controller not found: $controllerClass");
            }

            $controller = new $controllerClass();

            if (!method_exists($controller, $actionMethod)) {
                throw new \RuntimeException("Action not found: {$handler['controller']}@$actionMethod");
            }

            error_log("Executing route: {$this->method} {$this->route} -> {$handler['controller']}@$actionMethod");

            if (!empty($this->parameters)) {
                call_user_func_array([$controller, $actionMethod], $this->parameters);
            } else {
                $controller->$actionMethod();
            }
        } catch (\Exception $e) {
            error_log("Route execution error: " . $e->getMessage());
            $this->sendJson([
                'success' => false,
                'message' => 'Error processing request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function authorizeRoute(array $handler): void
    {
        if (!$this->isApiRoute()) {
            return;
        }

        if (!$this->isLoggedIn()) {
            $this->sendJson([
                'success' => false,
                'message' => 'Unauthorized. Please log in first.'
            ], 401);
        }

        $requiredPermissions = $this->resolveRequiredPermissions($handler);
        if (empty($requiredPermissions)) {
            return;
        }

        if (!$this->userHasAnyPermission($requiredPermissions)) {
            $this->sendJson([
                'success' => false,
                'message' => 'Forbidden. You do not have permission for this action.',
                'required_permissions' => $requiredPermissions
            ], 403);
        }
    }

    private function isApiRoute(): bool
    {
        return strpos($this->route, '/api/') === 0;
    }

    private function isLoggedIn(): bool
    {
        return isset($_SESSION['login']) && $_SESSION['login'] === true && !empty($_SESSION['user_id']);
    }

    private function userHasAnyPermission(array $permissionSlugs): bool
    {
        $normalized = [];
        foreach ($permissionSlugs as $slug) {
            if (!is_string($slug)) {
                continue;
            }

            $slug = strtolower(trim($slug));
            if ($slug !== '') {
                $normalized[] = $slug;
            }
        }

        $normalized = array_values(array_unique($normalized));
        if (empty($normalized)) {
            return true;
        }

        if (function_exists('hasAnyPermissionSlugs')) {
            return \hasAnyPermissionSlugs($normalized);
        }

        if ($this->isAdminSession()) {
            return true;
        }

        $permissions = $_SESSION['permissions'] ?? [];
        if (!is_array($permissions)) {
            return false;
        }

        $permissions = array_map(static fn ($slug) => strtolower((string) $slug), $permissions);

        foreach ($normalized as $slug) {
            if (in_array($slug, $permissions, true)) {
                return true;
            }
        }

        return false;
    }

    private function isAdminSession(): bool
    {
        $roleName = strtolower((string) ($_SESSION['role'] ?? $_SESSION['role_name'] ?? ''));
        $roleId = (int) ($_SESSION['role_id'] ?? 0);

        return $roleName === 'admin' || $roleId === 1;
    }

    private function resolveRequiredPermissions(array $handler): array
    {
        $controller = $handler['controller'] ?? '';
        $action = $handler['action'] ?? '';

        return match ($controller) {
            'ControllerDashboard' => ['dashboard.view'],
            'ControllerAttendance' => $this->permissionsForAttendanceAction($action),
            'ControllerEmployee' => $this->permissionsForEmployeeAction($action),
            'ControllerLeave' => $this->permissionsForLeaveAction($action),
            'ControllerReport' => $this->permissionsForReportAction($action),
            'ControllerUser' => $this->permissionsForUserAction($action),
            'ControllerRole' => $this->permissionsForRoleAction($action),
            'ControllerPermission' => $this->permissionsForPermissionAction($action),
            default => [],
        };
    }

    private function permissionsForAttendanceAction(string $action): array
    {
        return match ($action) {
            'show', 'today' => ['attendance.view'],
            'checkIn', 'checkOut', 'scan' => ['attendance.update', 'attendance.create'],
            default => ['attendance.view'],
        };
    }

    private function permissionsForEmployeeAction(string $action): array
    {
        return match ($action) {
            'index', 'show' => ['employee.view', 'employees.view'],
            'store' => ['employee.create', 'employees.create'],
            'update' => ['employee.update', 'employees.update'],
            'delete', 'destroy' => ['employee.delete', 'employees.delete'],
            default => ['employee.view', 'employees.view'],
        };
    }

    private function permissionsForLeaveAction(string $action): array
    {
        return match ($action) {
            'index' => ['leave.view'],
            'create' => ['leave.create'],
            'approve', 'reject' => ['leave.update', 'leave.approve', 'leave.reject'],
            default => ['leave.view'],
        };
    }

    private function permissionsForReportAction(string $action): array
    {
        return match ($action) {
            'dailyList' => ['report.view_daily', 'report.view'],
            'summary' => ['report.view_summary', 'report.view'],
            'detailedList' => ['report.view_detail', 'report.view'],
            'topEmployees' => ['report.view_top', 'report.view'],
            default => ['report.view'],
        };
    }

    private function permissionsForUserAction(string $action): array
    {
        return match ($action) {
            'show', 'getUserById' => ['user.view', 'users.view'],
            'create' => ['user.create', 'users.create'],
            'update' => ['user.update', 'users.update'],
            'delete' => ['user.delete', 'users.delete'],
            default => ['user.view', 'users.view'],
        };
    }

    private function permissionsForRoleAction(string $action): array
    {
        return match ($action) {
            'index', 'show', 'stats', 'search', 'permissions', 'permissionsGrouped', 'rolePermissions' => ['role.view', 'roles.view'],
            'store' => ['role.create', 'roles.manage'],
            'update', 'accept', 'updateStatus', 'updateRolePermissions' => ['role.update', 'roles.manage'],
            'destroy' => ['role.delete', 'roles.manage'],
            default => ['role.view', 'roles.view'],
        };
    }

    private function permissionsForPermissionAction(string $action): array
    {
        return match ($action) {
            'index', 'show', 'getByCategory', 'getById', 'getCategories', 'getPermissionsByRole', 'checkUserPermission' => ['permission.view', 'permissions.view'],
            'create' => ['permission.add', 'permission.create', 'permissions.manage'],
            'update' => ['permission.update', 'permissions.manage'],
            'delete' => ['permission.delete', 'permissions.manage'],
            'assignToRole', 'removeFromRole', 'assignMultipleToRole' => ['permission.update', 'permissions.manage'],
            default => ['permission.view', 'permissions.view'],
        };
    }

    private function notFound(): void
    {
        error_log("404 Not Found: {$this->method} {$this->route}");

        $this->sendJson([
            'success' => false,
            'message' => 'API endpoint not found',
            'requested_route' => $this->route,
            'method' => $this->method
        ], 404);
    }

    private function sendJson(array $data, int $statusCode = 200): void
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function printRoutes(): void
    {
        echo "\n====== Registered Routes ======\n";

        foreach ($this->routes as $method => $routes) {
            echo "\n$method:\n";
            foreach ($routes as $path => $handler) {
                $controller = $handler['controller'];
                $action = $handler['action'];
                echo "  $path -> $controller@$action\n";
            }
        }

        echo "\n";
    }
}
