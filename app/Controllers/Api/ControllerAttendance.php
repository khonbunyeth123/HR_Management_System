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

    public function scan()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['employee_id'])) {
            $this->sendJsonError('Employee ID required', 400);
            return;
        }

        $result = $this->service->scan((int)$data['employee_id']);

        $this->sendJson($result);
    }

    public function show(): void
    {
        try {
            // Pagination
            $page    = isset($_GET['paging_options']['page'])     ? (int)$_GET['paging_options']['page']     : 1;
            $perPage = isset($_GET['paging_options']['per_page']) ? (int)$_GET['paging_options']['per_page'] : 18;
            $offset  = ($page - 1) * $perPage;

            // Filters
            $filters  = $_GET['filters'] ?? [];
            $statusId = isset($filters['status_id']) ? (int)$filters['status_id'] : null;

            // Fetch records
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

    /**
     * Send JSON response helper
     */
    private function sendJson(array $data, int $statusCode = 200): void
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Send JSON error response helper
     */
    private function sendJsonError(string $message, int $statusCode = 500): void
    {
        $this->sendJson([
            'success' => false,
            'message' => $message,
        ], $statusCode);
    }
}