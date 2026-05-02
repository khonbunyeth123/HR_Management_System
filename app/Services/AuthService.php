<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Auth;

class AuthService
{
    private Auth $authModel;
    private const TOKEN_TTL_SECONDS = 2592000;

    public function __construct()
    {
        $this->authModel = new Auth();
    }

    public function adminLogin(array $credentials): array
    {
        $identifier = trim((string) ($credentials['identifier'] ?? $credentials['username'] ?? $credentials['email'] ?? ''));
        $password = trim((string) ($credentials['password'] ?? ''));

        if ($identifier === '' || $password === '') {
            return ['success' => false, 'message' => 'Username or email and password are required.', 'code' => 400];
        }

        $user = $this->authModel->findAdminByIdentifier($identifier);
        if (!$user || !password_verify($password, (string) $user['password'])) {
            return ['success' => false, 'message' => 'Invalid username, email, or password.', 'code' => 401];
        }

        $token = $this->issueToken('user', (int) $user['id']);
        $this->storeAdminSession($user, $token);

        return [
            'success' => true,
            'message' => 'Admin login successful.',
            'code' => 200,
            'token' => $token,
            'user' => [
                'id' => (int) $user['id'],
                'uuid' => $user['uuid'],
                'username' => $user['username'],
                'full_name' => $user['full_name'],
                'email' => $user['email'],
                'role' => $user['role_name'],
                'login_as' => 'user',
            ],
        ];
    }

    public function employeeLogin(array $credentials): array
    {
        $identifier = trim((string) ($credentials['identifier'] ?? $credentials['username'] ?? $credentials['email'] ?? ''));
        $password = trim((string) ($credentials['password'] ?? ''));

        if ($identifier === '' || $password === '') {
            return ['success' => false, 'message' => 'Username or email and password are required.', 'code' => 400];
        }

        $employee = $this->authModel->findEmployeeByIdentifier($identifier);
        if (!$employee || empty($employee['password']) || !password_verify($password, (string) $employee['password'])) {
            return ['success' => false, 'message' => 'Invalid username, email, or password.', 'code' => 401];
        }

        $token = $this->issueToken('employee', (int) $employee['id']);
        $this->storeEmployeeSession($employee, $token);

        return [
            'success' => true,
            'message' => 'Employee login successful.',
            'code' => 200,
            'token' => $token,
            'employee' => [
                'id' => (int) $employee['id'],
                'uuid' => $employee['uuid'],
                'username' => $employee['username'],
                'full_name' => $employee['full_name'],
                'email' => $employee['email'],
                'login_as' => 'employee',
            ],
        ];
    }

    public function logout(): array
    {
        $token = $this->readBearerToken();
        if ($token === null) {
            $token = $_SESSION['access_token'] ?? null;
        }

        if (is_string($token) && $token !== '') {
            $this->authModel->revokeAccessToken($token);
        }

        session_destroy();
        return ['success' => true, 'message' => 'Logged out successfully.', 'code' => 200];
    }

    public function adminMe(): array
    {
        if (($_SESSION['auth_type'] ?? '') !== 'user' || empty($_SESSION['user_id'])) {
            return ['success' => false, 'message' => 'Unauthenticated admin.', 'code' => 401];
        }

        return [
            'success' => true,
            'code' => 200,
            'user' => [
                'id' => $_SESSION['user_id'],
                'uuid' => $_SESSION['uuid'],
                'username' => $_SESSION['username'],
                'full_name' => $_SESSION['full_name'],
                'role' => $_SESSION['role'],
                'login_as' => 'user',
            ],
        ];
    }

    public function employeeMe(): array
    {
        if (($_SESSION['auth_type'] ?? '') !== 'employee' || empty($_SESSION['employee_id'])) {
            return ['success' => false, 'message' => 'Unauthenticated employee.', 'code' => 401];
        }

        return [
            'success' => true,
            'code' => 200,
            'employee' => [
                'id' => $_SESSION['employee_id'],
                'uuid' => $_SESSION['uuid'],
                'username' => $_SESSION['username'],
                'full_name' => $_SESSION['full_name'],
                'email' => $_SESSION['email'] ?? null,
                'login_as' => 'employee',
            ],
        ];
    }

    private function issueToken(string $type, int $id): string
    {
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + self::TOKEN_TTL_SECONDS);
        $this->authModel->createAccessToken($type, $id, $token, $expiresAt);
        return $token;
    }

    private function storeAdminSession(array $user, string $token): void
    {
        $_SESSION['login'] = true;
        $_SESSION['auth_type'] = 'user';
        $_SESSION['access_token'] = $token;
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['employee_id'] = null;
        $_SESSION['uuid'] = $user['uuid'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role_name'];
        $_SESSION['role_id'] = $user['role_id'];
    }

    private function storeEmployeeSession(array $employee, string $token): void
    {
        $_SESSION['login'] = true;
        $_SESSION['auth_type'] = 'employee';
        $_SESSION['access_token'] = $token;
        $_SESSION['user_id'] = null;
        $_SESSION['employee_id'] = (int) $employee['id'];
        $_SESSION['uuid'] = $employee['uuid'];
        $_SESSION['username'] = $employee['username'];
        $_SESSION['full_name'] = $employee['full_name'];
        $_SESSION['email'] = $employee['email'];
        $_SESSION['role'] = null;
        $_SESSION['role_id'] = null;
    }

    private function readBearerToken(): ?string
    {
        $headers = getallheaders();
        $auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';

        if (!is_string($auth) || !str_starts_with($auth, 'Bearer ')) {
            return null;
        }

        $token = trim(substr($auth, 7));
        return $token !== '' ? $token : null;
    }
}
