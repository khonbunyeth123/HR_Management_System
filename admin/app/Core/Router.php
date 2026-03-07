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
        $route = str_replace('/api', '', $uri);
        $route = explode('?', $route)[0];
        $route = rtrim($route, '/') ?: '/';
        return $route;
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
