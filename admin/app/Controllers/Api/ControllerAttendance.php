<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Models\Attendance;

class ControllerAttendance
{
    private Attendance $attendanceModel;

    public function __construct()
    {
        try {
            $this->attendanceModel = new Attendance();
        } catch (\Exception $e) {
            error_log("ControllerAttendance - Initialization Error: " . $e->getMessage());
            $this->sendJsonError("Failed to initialize attendance", 500);
        }
    }

    public function scan()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['employee_id'])) {
            $this->json(['error' => 'Employee ID required']);
            return;
        }

        $result = $this->service->scan((int)$data['employee_id']);

        $this->json($result);
    }


    public function show(): void
    {
        try {
            // Pagination
            $page = isset($_GET['paging_options']['page']) ? (int)$_GET['paging_options']['page'] : 1;
            $perPage = isset($_GET['paging_options']['per_page']) ? (int)$_GET['paging_options']['per_page'] : 18;
            $offset = ($page - 1) * $perPage;

            // Filters
            $filters = $_GET['filters'] ?? [];
            $statusId = $filters['status_id'] ?? null;
            $statusId = $statusId !== null ? (int)$statusId : null; // <<< important cast

            // Fetch records
            $records = $this->attendanceModel->getList($perPage, $offset, $statusId);
            $total = $this->attendanceModel->countAll($statusId);

            $this->sendJson([
                'success' => true,
                'data' => [
                    'attendance_records' => $records
                ],
                'pagination' => [
                    'total' => $total,
                    'total_pages' => (int)ceil($total / $perPage)
                ]
            ]);
        } catch (\Throwable $e) {
            $this->sendJson([
                'success' => false,
                'message' => 'Error in show',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // POST /api/attendance/checkin
    // public function checkIn(): void
    // {
    //     $input = json_decode(file_get_contents('php://input'), true);

    //     if (empty($input['employee_id'])) {
    //         $this->sendJsonError("Employee ID is required", 400);
    //     }

    //     $result = $this->attendanceModel->checkIn((int)$input['employee_id']);

    //     if ($result) {
    //         $this->sendJson([
    //             'success' => true,
    //             'message' => 'Check-in successful',
    //         ]);
    //     } else {
    //         $this->sendJsonError("Check-in failed", 500);
    //     }
    // }

    // POST /api/attendance/checkout
    // public function checkOut(): void
    // {
    //     $input = json_decode(file_get_contents('php://input'), true);

    //     if (empty($input['employee_id'])) {
    //         $this->sendJsonError("Employee ID is required", 400);
    //     }

    //     $result = $this->attendanceModel->checkOut((int)$input['employee_id']);

    //     if ($result) {
    //         $this->sendJson([
    //             'success' => true,
    //             'message' => 'Check-out successful',
    //         ]);
    //     } else {
    //         $this->sendJsonError("Check-out failed", 500);
    //     }
    // }

    // GET /api/attendance/today?employee_id=5
    // public function today(): void
    // {
    //     $employeeId = $_GET['employee_id'] ?? null;

    //     if (!$employeeId) {
    //         $this->sendJsonError("Employee ID is required", 400);
    //     }

    //     $attendance = $this->attendanceModel->getTodayAttendance((int)$employeeId);

    //     if ($attendance) {
    //         $this->sendJson([
    //             'success' => true,
    //             'message' => "Today's attendance retrieved",
    //             'data' => $attendance
    //         ]);
    //     } else {
    //         $this->sendJsonError("No attendance record found for today", 404);
    //     }
    // }

    // GET /api/attendance/show

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
