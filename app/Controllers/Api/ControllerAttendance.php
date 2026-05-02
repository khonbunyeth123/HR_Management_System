<?php
declare(strict_types=1);
namespace App\Controllers\Api;

use App\Models\Attendance;
use App\Services\AttendanceService;

class ControllerAttendance
{
    private Attendance $attendanceModel;
    private AttendanceService $service;

    public function __construct()
    {
        try {
            $this->attendanceModel = new Attendance();
            $this->service = new AttendanceService();
        } catch (\Exception $e) {
            error_log("ControllerAttendance - Initialization Error: " . $e->getMessage());
            $this->sendJsonError("Failed to initialize attendance", 500);
        }
    }

    /**
     * POST /api/attendance/scan
     * Called by Flutter after employee scans the QR code.
     * Mobile clients authenticate first, then send only the QR payload.
     */
    public function scan(): void
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!is_array($data)) {
            $this->sendJsonError('Invalid request body', 400);
            return;
        }

        // 1. Resolve employee from the authenticated token/session, then fall back
        // to explicit identifiers for non-mobile callers.
        $employee = $this->resolveScanEmployee($data);
        if (!$employee) {
            $this->sendJson([
                'success' => false,
                'message' => 'Employee not found. Please log in again or send a valid employee identifier.',
            ], 404);
            return;
        }

        // 2. Validate QR code (static secret)
        $qrCode = $data['qr_code'] ?? '';

        if ($qrCode !== 'DOORSTEP_ATTENDANCE') {
            $this->sendJsonError('Invalid QR code', 400);
            return;
        }

        // 3. Process scan using the employee table primary key.
        $result = $this->service->scan((int) $employee['id']);

        if (isset($result['error'])) {
            $this->sendJson(['success' => false, 'message' => $result['error']], 400);
            return;
        }

        $this->sendJson([
            'success'        => true,
            'message'        => $result['label'] . ' recorded at ' . $result['time'],
            'scan_type'      => $result['scan_type'],
            'label'          => $result['label'],
            'time'           => $result['time'],
            'standard_time'  => $result['standard_time'],
            'employee_name'  => $employee['full_name'],
            'employee_id'    => (int) $employee['id'],
        ]);
    }

    public function qr(): void
    {
        $this->sendJson([
            'success'  => true,
            'qr_value' => 'DOORSTEP_ATTENDANCE',
            'label'    => 'Scan to record attendance',
        ]);
    }

    public function show(): void
    {
        try {
            $page    = isset($_GET['paging_options']['page'])     ? (int)$_GET['paging_options']['page']     : 1;
            $perPage = isset($_GET['paging_options']['per_page']) ? (int)$_GET['paging_options']['per_page'] : 18;
            $offset  = ($page - 1) * $perPage;

            $filters  = $_GET['filters'] ?? [];
            $statusId = isset($filters['status_id']) ? (int)$filters['status_id'] : null;

            $records = $this->attendanceModel->getList($perPage, $offset, $statusId);
            $total   = $this->attendanceModel->countAll($statusId);

            $this->sendJson([
                'success' => true,
                'data' => [
                    'attendance_records' => $records
                ],
                'pagination' => [
                    'total'       => $total,
                    'total_pages' => (int)ceil($total / $perPage)
                ]
            ]);
        } catch (\Throwable $e) {
            error_log("ControllerAttendance::show - " . $e->getMessage());
            $this->sendJson([
                'success' => false,
                'message' => 'Error in show',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function checkin(): void
    {
        $data    = $this->service->getCheckinPageData();
        $message = '';
        $msgType = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $employeeId = intval($_POST['employee_id'] ?? 0);
            if (!$employeeId) {
                $message = 'Please select your name.';
                $msgType = 'error';
            } else {
                $result  = $this->service->checkin($employeeId);
                $message = $result['error'] ?? $result['message'];
                $msgType = $result['type'];
            }
        }

        $slot      = $data['slot'];
        $employees = $data['employees'];
        require __DIR__ . '/../../../resources/views/checkin.php';
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function resolveScanEmployee(array $data): ?array
    {
        $sessionEmployeeId = isset($_SESSION['employee_id']) ? (int) $_SESSION['employee_id'] : 0;
        if ($sessionEmployeeId > 0) {
            return $this->attendanceModel->findActiveEmployeeById($sessionEmployeeId);
        }

        $employeeUuid = trim((string) ($data['employee_uuid'] ?? ''));
        if ($employeeUuid !== '') {
            return $this->attendanceModel->findActiveEmployeeByUuid($employeeUuid);
        }

        $employeeId = isset($data['employee_id']) ? (int) $data['employee_id'] : 0;
        if ($employeeId > 0) {
            return $this->attendanceModel->findActiveEmployeeById($employeeId);
        }

        return null;
    }

    public function history(): void
    {
        $employeeId = (int)($_SESSION['employee_id'] ?? 0);
        if (!$employeeId) {
            $this->sendJsonError('Unauthorized', 401);
            return;
        }

        $page    = (int)($_GET['page']     ?? 1);
        $perPage = (int)($_GET['per_page'] ?? 20);

        $result = $this->service->getHistory($employeeId, $page, $perPage);

        $this->sendJson([
            'success' => true,
            'data'    => $result['records'],
            'pagination' => [
                'total'       => $result['total'],
                'page'        => $page,
                'per_page'    => $perPage,
                'total_pages' => $result['total_pages'],
            ],
        ]);
    }

    private function sendJson(array $data, int $statusCode = 200): void
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function sendJsonError(string $message, int $statusCode = 500): void
    {
        $this->sendJson([
            'success' => false,
            'message' => $message,
        ], $statusCode);
    }
}
