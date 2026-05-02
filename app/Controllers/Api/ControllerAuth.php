<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Services\AuthService;
use App\Helpers\Response;

class ControllerAuth
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function login(): void
    {
        $this->adminLogin();
    }

    public function adminLogin(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!is_array($data)) {
            $data = [];
        }

        $result = $this->authService->adminLogin($data);

        Response::json($result, $result['code']);
    }

    public function employeeLogin(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!is_array($data)) {
            $data = [];
        }

        $result = $this->authService->employeeLogin($data);

        Response::json($result, $result['code']);
    }

    public function logout(): void
    {
        $result = $this->authService->logout();
        Response::json($result, $result['code']);
    }

    public function me(): void
    {
        $this->adminMe();
    }

    public function adminMe(): void
    {
        $result = $this->authService->adminMe();
        Response::json($result, $result['code']);
    }

    public function employeeMe(): void
    {
        $result = $this->authService->employeeMe();
        Response::json($result, $result['code']);
    }
}   
