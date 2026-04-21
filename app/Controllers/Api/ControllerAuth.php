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
        $data     = json_decode(file_get_contents('php://input'), true);
        $username = trim($data['username'] ?? '');
        $password = trim($data['password'] ?? '');

        $result = $this->authService->login($username, $password);

        Response::json($result, $result['code']);
    }

    public function logout(): void
    {
        $result = $this->authService->logout();
        Response::json($result, $result['code']);
    }

    public function me(): void
    {
        $result = $this->authService->me();
        Response::json($result, $result['code']);
    }
}   