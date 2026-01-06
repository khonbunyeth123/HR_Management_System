<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Models\Dashboard;

class ControllerDashboard
{
    private Dashboard $dashboard;

    public function __construct()
    {
        $this->dashboard = new Dashboard();
    }

    /**
     * Return summary stats for the dashboard
     * GET /api/dashboard/summary
     */
    public function summary(): void
    {
        try {
            $data = [
                'total_employees'  => $this->dashboard->totalEmployees(),
                'active_employees' => $this->dashboard->activeEmployees(),
                'pending_leaves'   => $this->dashboard->pendingLeaves(),
                'on_leave_today'   => $this->dashboard->onLeaveToday(),
            ];

            header('Content-Type: application/json');
            http_response_code(200);
            echo json_encode(['success' => true, 'data' => $data]);
            exit;
        } catch (\Exception $e) {
            error_log("Dashboard Summary Error: " . $e->getMessage());
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error']);
            exit;
        }
    }

    /**
     * Return recent leaves
     * GET /api/dashboard/recent-leaves
     */
    public function recentLeaves(): void
    {
        try {
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
            $data = $this->dashboard->recentLeaves($limit);
            
            header('Content-Type: application/json');
            http_response_code(200);
            echo json_encode(['success' => true, 'data' => $data]);
            exit;
        } catch (\Exception $e) {
            error_log("Recent Leaves Error: " . $e->getMessage());
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error']);
            exit;
        }
    }

    /**
     * Return department stats
     * GET /api/dashboard/department
     */
    public function department(): void
    {
        try {
            $data = $this->dashboard->departmentStats();
            
            header('Content-Type: application/json');
            http_response_code(200);
            echo json_encode(['success' => true, 'data' => $data]);
            exit;
        } catch (\Exception $e) {
            error_log("Department Stats Error: " . $e->getMessage());
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error']);
            exit;
        }
    }
}