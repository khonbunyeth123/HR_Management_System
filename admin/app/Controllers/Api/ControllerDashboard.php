<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Models\Dashboard;

class ControllerDashboard
{
    private Dashboard $dashboardModel;

    public function __construct()
    {
        try {
            $this->dashboardModel = new Dashboard();
        } catch (\Exception $e) {
            error_log("ControllerDashboard - Initialization Error: " . $e->getMessage());
            $this->sendJsonError("Failed to initialize dashboard", 500);
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

    /**
     * GET /api/dashboard/summary
     * Returns: total_employees, active_employees, pending_leaves, on_leave_today
     */
    public function summary(): void
    {
        try {
            $stats = $this->dashboardModel->getSummaryStats();

            error_log("Dashboard summary: " . json_encode($stats));

            $this->sendJson([
                'success' => true,
                'message' => 'Dashboard summary retrieved',
                'data' => $stats
            ], 200);
        } catch (\Exception $e) {
            error_log("Dashboard summary error: " . $e->getMessage());
            $this->sendJsonError("Error loading statistics", 500);
        }
    }

    /**
     * GET /api/dashboard/department
     * Returns: array of departments with name, count, and percentage
     */
    public function department(): void
    {
        try {
            $departments = $this->dashboardModel->departmentStats();

            error_log("Department stats: " . json_encode($departments));

            $this->sendJson([
                'success' => true,
                'message' => 'Department statistics retrieved',
                'data' => $departments
            ], 200);
        } catch (\Exception $e) {
            error_log("Department stats error: " . $e->getMessage());
            $this->sendJsonError("Error loading departments", 500);
        }
    }

    /**
     * GET /api/dashboard/recent-leaves
     * Optional query param: ?limit=10 (default: 5)
     * Returns: array of recent leave applications
     */
    public function recentLeaves(): void
    {
        try {
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
            $leaves = $this->dashboardModel->recentLeaves($limit);

            error_log("Recent leaves: " . json_encode($leaves));

            $this->sendJson([
                'success' => true,
                'message' => 'Recent leaves retrieved',
                'data' => $leaves
            ], 200);
        } catch (\Exception $e) {
            error_log("Recent leaves error: " . $e->getMessage());
            $this->sendJsonError("Error loading leave requests", 500);
        }
    }
}