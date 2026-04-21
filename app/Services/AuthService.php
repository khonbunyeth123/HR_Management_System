<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Auth;

class AuthService
{
    private Auth $authModel;

    public function __construct()
    {
        $this->authModel = new Auth();
    }

    public function login(string $username, string $password): array
    {
        if (empty($username) || empty($password)) {
            return ['success' => false, 'message' => 'Username and password are required.', 'code' => 400];
        }

        $user = $this->authModel->findByUsername($username);

        if (!$user) {
            return ['success' => false, 'message' => 'Invalid username or password.', 'code' => 401];
        }

        // Check if account is active
        if ((int)$user['status_id'] !== 1) {
            return ['success' => false, 'message' => 'Your account is inactive. Please contact admin.', 'code' => 403];
        }

        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Invalid username or password.', 'code' => 401];
        }

        // Generate token and save to login_session
        $token = bin2hex(random_bytes(32));
        $this->authModel->updateLoginSession($user['id'], $token);

        // Store session
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['uuid']      = $user['uuid'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role']      = $user['role_name'];
        $_SESSION['login']     = true;

        return [
            'success' => true,
            'message' => 'Login successful.',
            'code'    => 200,
            'token'   => $token,
            'user'    => [
                'id'        => $user['id'],
                'uuid'      => $user['uuid'],
                'username'  => $user['username'],
                'full_name' => $user['full_name'],
                'email'     => $user['email'],
                'role'      => $user['role_name'],
            ],
        ];
    }

    public function logout(): array
    {
        if (!empty($_SESSION['user_id'])) {
            // Clear login_session in DB
            $this->authModel->updateLoginSession((int)$_SESSION['user_id'], null);
        }

        session_destroy();
        return ['success' => true, 'message' => 'Logged out successfully.', 'code' => 200];
    }

    public function me(): array
    {
        if (empty($_SESSION['user_id'])) {
            return ['success' => false, 'message' => 'Unauthenticated.', 'code' => 401];
        }

        return [
            'success' => true,
            'code'    => 200,
            'user'    => [
                'id'        => $_SESSION['user_id'],
                'uuid'      => $_SESSION['uuid'],
                'username'  => $_SESSION['username'],
                'full_name' => $_SESSION['full_name'],
                'role'      => $_SESSION['role'],
            ],
        ];
    }
}