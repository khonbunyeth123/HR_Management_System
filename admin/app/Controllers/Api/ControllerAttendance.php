<?php
declare(strict_types=1);
namespace App\Controllers\Api;

use App\Models\Attendance;

class ControllerAttendace{
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