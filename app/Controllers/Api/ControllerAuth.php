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
    public function __construct()
    {
        $this->authService = new AuthService();
        $this->notificationService = new NotificationService();
    }
    public function login(): void { $this->adminLogin(); }
    public function adminLogin(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!is_array($data)) $data = [];
        $result = $this->authService->adminLogin($data);
        Response::json($result, $result['code']);
    }
    public function employeeLogin(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!is_array($data)) $data = [];
        $result = $this->authService->employeeLogin($data);
        if ($result['success'] && !empty($data['fcm_token'])) {
            $employeeId = $result['employee']['id'] ?? null;
            if ($employeeId) {
                $this->notificationService->saveFcmToken((int)$employeeId, $data['fcm_token']);
            }
        }
        Response::json($result, $result['code']);
    }
    public function logout(): void
    {
        $result = $this->authService->logout();
        Response::json($result, $result['code']);
    }
    public function me(): void { $this->adminMe(); }
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
    public function saveFcmToken(): void
    {
        if (($_SESSION['auth_type'] ?? '') !== 'employee' || empty($_SESSION['employee_id'])) {
            Response::json(['success' => false, 'message' => 'Unauthorized.'], 401);
            return;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $fcmToken = trim((string)($data['fcm_token'] ?? ''));
        if ($fcmToken === '') {
            Response::json(['success' => false, 'message' => 'fcm_token is required.'], 400);
            return;
        }
        $ok = $this->notificationService->saveFcmToken((int)$_SESSION['employee_id'], $fcmToken);
        Response::json(['success' => $ok, 'message' => $ok ? 'FCM token saved.' : 'Failed to save token.'], $ok ? 200 : 500);
    }
}
