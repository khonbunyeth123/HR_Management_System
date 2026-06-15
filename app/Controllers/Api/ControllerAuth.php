<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Services\AuthService;
use App\Services\NotificationService;
use App\Helpers\Response;

class ControllerAuth
{
    private AuthService $authService;
    private NotificationService $notificationService;

    // FIX 1: cap request body size to prevent memory exhaustion
    private const MAX_BODY_BYTES = 4096;
    // FIX 2: FCM tokens are typically ~160 chars; cap at 256 to be safe
    private const FCM_MAX_LEN    = 256;

    public function __construct()
    {
        $this->authService         = new AuthService();
        $this->notificationService = new NotificationService();
    }

    // -----------------------------------------------------------------------
    // Admin login
    // -----------------------------------------------------------------------

    public function login(): void   { $this->adminLogin(); }

    public function adminLogin(): void
    {
        $data   = $this->readJsonBody();
        $result = $this->authService->adminLogin($data);
        Response::json($result, $result['code']);
    }

    // -----------------------------------------------------------------------
    // Employee login
    // -----------------------------------------------------------------------

    public function employeeLogin(): void
    {
        $data   = $this->readJsonBody();
        $result = $this->authService->employeeLogin($data);

        // FIX 3: validate FCM token before saving — length + basic format check
        if ($result['success'] && !empty($data['fcm_token'])) {
            $fcmToken   = (string) $data['fcm_token'];
            $employeeId = $result['employee']['id'] ?? null;

            if ($employeeId && $this->isValidFcmToken($fcmToken)) {
                $this->notificationService->saveFcmToken((int) $employeeId, $fcmToken);
            }
        }

        Response::json($result, $result['code']);
    }

    // -----------------------------------------------------------------------
    // Logout
    // -----------------------------------------------------------------------

    public function logout(): void
    {
        $result = $this->authService->logout();
        Response::json($result, $result['code']);
    }

    // -----------------------------------------------------------------------
    // "Me" endpoints
    // -----------------------------------------------------------------------

    public function me(): void      { $this->adminMe(); }

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

    // -----------------------------------------------------------------------
    // Password Resets
    // -----------------------------------------------------------------------

    /**
     * Request a password reset link (API).
     */
    public function forgotPassword(): void
    {
        $data = $this->readJsonBody();
        $email = (string) ($data['email'] ?? '');
        
        $result = $this->authService->requestPasswordReset($email);
        Response::json($result, $result['code']);
    }

    /**
     * Submit a password reset (API).
     */
    public function resetPassword(): void
    {
        $data = $this->readJsonBody();
        $email = (string) ($data['email'] ?? '');
        $token = (string) ($data['token'] ?? '');
        $password = (string) ($data['password'] ?? '');
        
        $result = $this->authService->resetPassword($email, $token, $password);
        Response::json($result, $result['code']);
    }

    // -----------------------------------------------------------------------
    // Save FCM token (standalone endpoint)
    // -----------------------------------------------------------------------

    public function saveFcmToken(): void
    {
        if (($_SESSION['auth_type'] ?? '') !== 'employee' || empty($_SESSION['employee_id'])) {
            Response::json(['success' => false, 'message' => 'Unauthorized.'], 401);
            return;
        }

        $data     = $this->readJsonBody();
        $fcmToken = trim((string) ($data['fcm_token'] ?? ''));

        if ($fcmToken === '') {
            Response::json(['success' => false, 'message' => 'fcm_token is required.'], 400);
            return;
        }

        // FIX 3: validate FCM token format/length before persisting
        if (!$this->isValidFcmToken($fcmToken)) {
            Response::json(['success' => false, 'message' => 'Invalid fcm_token.'], 422);
            return;
        }

        $ok = $this->notificationService->saveFcmToken((int) $_SESSION['employee_id'], $fcmToken);
        Response::json(
            ['success' => $ok, 'message' => $ok ? 'FCM token saved.' : 'Failed to save token.'],
            $ok ? 200 : 500
        );
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    /**
     * Read and decode the JSON request body with a size cap.
     * FIX 1: prevents oversized payloads from exhausting PHP memory.
     */
    private function readJsonBody(): array
    {
        $raw = file_get_contents('php://input', false, null, 0, self::MAX_BODY_BYTES + 1);

        if ($raw === false || $raw === '') {
            return [];
        }

        // FIX 1: reject bodies larger than the cap
        if (strlen($raw) > self::MAX_BODY_BYTES) {
            http_response_code(413);
            echo json_encode(['success' => false, 'message' => 'Request body too large.']);
            exit;
        }

        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    /**
     * Basic FCM token validation — must be non-empty, within length limit,
     * and contain only safe characters.
     * FIX 2: prevents arbitrary strings from being stored in the DB.
     */
    private function isValidFcmToken(string $token): bool
    {
        if ($token === '' || strlen($token) > self::FCM_MAX_LEN) {
            return false;
        }

        // FCM tokens are alphanumeric with hyphens, underscores, and colons
        return (bool) preg_match('/^[A-Za-z0-9\-_:]+$/', $token);
    }
}