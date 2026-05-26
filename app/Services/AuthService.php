<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Auth;

class AuthService
{
    private Auth $authModel;

    private const TOKEN_TTL_SECONDS  = 2592000; // 30 days
    private const MAX_LOGIN_ATTEMPTS = 10;       // per window
    private const RATE_WINDOW_SECONDS = 60;
    private const FCM_TOKEN_MAX_LEN  = 256;

    public function __construct()
    {
        $this->authModel = new Auth();
    }

    // -----------------------------------------------------------------------
    // Login
    // -----------------------------------------------------------------------

    public function adminLogin(array $credentials): array
    {
        // FIX 1: rate-limit by IP before touching the DB
        $ip = $this->clientIp();
        if ($this->isRateLimited("admin_login_{$ip}")) {
            return ['success' => false, 'message' => 'Too many login attempts. Please try again later.', 'code' => 429];
        }

        $identifier = trim((string) ($credentials['identifier'] ?? $credentials['username'] ?? $credentials['email'] ?? ''));
        $password   = trim((string) ($credentials['password'] ?? ''));

        if ($identifier === '' || $password === '') {
            return ['success' => false, 'message' => 'Username/email and password are required.', 'code' => 400];
        }

        $user = $this->authModel->findAdminByIdentifier($identifier);

        // FIX 2: always run password_verify even on miss (prevents timing attack / user enumeration)
        $hash = $user['password'] ?? '$2y$12$invalidsaltinvalidsaltinvalidsaltinvali';
        if (!$user || !password_verify($password, (string) $hash)) {
            return ['success' => false, 'message' => 'Invalid credentials.', 'code' => 401];
        }

        [$token, $tokenId] = $this->issueToken('user', (int) $user['id']);
        $this->storeAdminSession($user, $tokenId);

        return [
            'success' => true,
            'message' => 'Login successful.',
            'code'    => 200,
            'token'   => $token,
            'user'    => [
                'id'        => (int) $user['id'],
                'uuid'      => $user['uuid'],
                'username'  => $user['username'],
                'full_name' => $user['full_name'],
                'email'     => $user['email'],
                'role'      => $user['role_name'],
                'login_as'  => 'user',
            ],
        ];
    }

    public function employeeLogin(array $credentials): array
    {
        // FIX 1: rate-limit by IP
        $ip = $this->clientIp();
        if ($this->isRateLimited("employee_login_{$ip}")) {
            return ['success' => false, 'message' => 'Too many login attempts. Please try again later.', 'code' => 429];
        }

        $identifier = trim((string) ($credentials['identifier'] ?? $credentials['username'] ?? $credentials['email'] ?? ''));
        $password   = trim((string) ($credentials['password'] ?? ''));

        if ($identifier === '' || $password === '') {
            return ['success' => false, 'message' => 'Username/email and password are required.', 'code' => 400];
        }

        $employee = $this->authModel->findEmployeeByIdentifier($identifier);

        // FIX 2: always run password_verify to prevent timing attacks
        $hash = $employee['password'] ?? '$2y$12$invalidsaltinvalidsaltinvalidsaltinvali';
        if (!$employee || empty($employee['password']) || !password_verify($password, (string) $hash)) {
            return ['success' => false, 'message' => 'Invalid credentials.', 'code' => 401];
        }

        [$token, $tokenId] = $this->issueToken('employee', (int) $employee['id']);
        $this->storeEmployeeSession($employee, $tokenId);

        return [
            'success'  => true,
            'message'  => 'Login successful.',
            'code'     => 200,
            'token'    => $token,
            'employee' => [
                'id'        => (int) $employee['id'],
                'uuid'      => $employee['uuid'],
                'username'  => $employee['username'],
                'full_name' => $employee['full_name'],
                'email'     => $employee['email'],
                'login_as'  => 'employee',
            ],
        ];
    }

    // -----------------------------------------------------------------------
    // Logout
    // -----------------------------------------------------------------------

    public function logout(): array
    {
        // FIX 3: use token ID from session (not raw token) to match new Auth model
        $tokenId = isset($_SESSION['access_token_id']) ? (int) $_SESSION['access_token_id'] : null;

        if ($tokenId !== null && $tokenId > 0) {
            $this->authModel->revokeAccessToken($tokenId);
        }

        // FIX 4: also revoke ALL tokens for the owner so other sessions are invalidated
        $authType = $_SESSION['auth_type'] ?? null;
        $ownerId  = $authType === 'user'
            ? (int) ($_SESSION['user_id']     ?? 0)
            : (int) ($_SESSION['employee_id'] ?? 0);

        if ($authType !== null && $ownerId > 0) {
            $this->authModel->revokeAllTokensFor($authType, $ownerId);
        }

        // Destroy session cleanly
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();

        return ['success' => true, 'message' => 'Logged out successfully.', 'code' => 200];
    }

    // -----------------------------------------------------------------------
    // "Me" endpoints
    // -----------------------------------------------------------------------

    public function adminMe(): array
    {
        if (($_SESSION['auth_type'] ?? '') !== 'user' || empty($_SESSION['user_id'])) {
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
                'login_as'  => 'user',
            ],
        ];
    }

    public function employeeMe(): array
    {
        if (($_SESSION['auth_type'] ?? '') !== 'employee' || empty($_SESSION['employee_id'])) {
            return ['success' => false, 'message' => 'Unauthenticated.', 'code' => 401];
        }

        return [
            'success'  => true,
            'code'     => 200,
            'employee' => [
                'id'        => $_SESSION['employee_id'],
                'uuid'      => $_SESSION['uuid'],
                'username'  => $_SESSION['username'],
                'full_name' => $_SESSION['full_name'],
                'email'     => $_SESSION['email'] ?? null,
                'login_as'  => 'employee',
            ],
        ];
    }

    // -----------------------------------------------------------------------
    // Token issuance
    // -----------------------------------------------------------------------

    /**
     * Generate a cryptographically random token, persist its hash, and return
     * both the raw token (for the client) and the DB row ID (for the session).
     *
     * @return array{0: string, 1: int}  [rawToken, tokenId]
     */
    private function issueToken(string $type, int $id): array
    {
        // FIX 5: bin2hex(random_bytes(32)) = 64-char cryptographically secure token
        $token     = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + self::TOKEN_TTL_SECONDS);

        $tokenId = $this->authModel->createAccessToken($type, $id, $token, $expiresAt);

        return [$token, $tokenId];
    }

    // -----------------------------------------------------------------------
    // Session helpers
    // -----------------------------------------------------------------------

    private function storeAdminSession(array $user, int $tokenId): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }

        $_SESSION['login']            = true;
        $_SESSION['auth_type']        = 'user';
        // FIX 6: store token row ID only — never the raw token
        $_SESSION['access_token_id']  = $tokenId;
        $_SESSION['user_id']          = (int) $user['id'];
        $_SESSION['employee_id']      = null;
        $_SESSION['uuid']             = $user['uuid'];
        $_SESSION['username']         = $user['username'];
        $_SESSION['full_name']        = $user['full_name'];
        $_SESSION['email']            = $user['email'];
        $_SESSION['role']             = $user['role_name'];
        $_SESSION['role_id']          = $user['role_id'];
    }

    private function storeEmployeeSession(array $employee, int $tokenId): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }

        $_SESSION['login']            = true;
        $_SESSION['auth_type']        = 'employee';
        // FIX 6: store token row ID only — never the raw token
        $_SESSION['access_token_id']  = $tokenId;
        $_SESSION['user_id']          = null;
        $_SESSION['employee_id']      = (int) $employee['id'];
        $_SESSION['uuid']             = $employee['uuid'];
        $_SESSION['username']         = $employee['username'];
        $_SESSION['full_name']        = $employee['full_name'];
        $_SESSION['email']            = $employee['email'];
        $_SESSION['role']             = null;
        $_SESSION['role_id']          = null;
    }

    // -----------------------------------------------------------------------
    // Rate limiting (flat-file, lock-safe)
    // -----------------------------------------------------------------------

    private function isRateLimited(string $key): bool
    {
        $file = sys_get_temp_dir() . '/rl_' . md5($key) . '.json';
        $now  = time();
        $data = ['count' => 0, 'reset' => $now + self::RATE_WINDOW_SECONDS];

        if (file_exists($file)) {
            $raw = file_get_contents($file);
            if ($raw !== false) {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) $data = $decoded;
            }
        }

        if ($now > (int) $data['reset']) {
            $data = ['count' => 0, 'reset' => $now + self::RATE_WINDOW_SECONDS];
        }

        $data['count']++;
        file_put_contents($file, json_encode($data), LOCK_EX);

        return $data['count'] > self::MAX_LOGIN_ATTEMPTS;
    }

    private function clientIp(): string
    {
        // Prefer the real IP; fall back to REMOTE_ADDR
        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'] as $key) {
            if (!empty($_SERVER[$key])) {
                return $_SERVER[$key];
            }
        }
        return 'unknown';
    }

    // -----------------------------------------------------------------------
    // Bearer token reader (used by logout fallback)
    // -----------------------------------------------------------------------

    private function readBearerToken(): ?string
    {
        $headers = getallheaders();
        $auth    = $headers['Authorization'] ?? $headers['authorization'] ?? '';

        if (!is_string($auth) || !str_starts_with($auth, 'Bearer ')) {
            return null;
        }

        $token = trim(substr($auth, 7));
        return $token !== '' ? $token : null;
    }
}