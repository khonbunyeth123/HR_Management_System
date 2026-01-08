<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Models\Dashboard;

class ControllerDashboard
{
    private Dashboard $dashboardModel;

    public function __construct()
    {
        $this->dashboardModel = new Dashboard();
    }

    /**
     * GET /api/dashboard/summary
     */
        public function summary()
        {
            try {
                header('Content-Type: application/json');
                
                $stats = $this->dashboardModel->getSummaryStats();

                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Dashboard summary retrieved',
                    'data' => $stats
                ]);
                exit;
            } catch (\Exception $e) {
                error_log("Dashboard summary error: " . $e->getMessage());
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Error loading statistics',
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                exit;
            }
        }

    /**
     * GET /api/dashboard/department
     */
    public function department()
    {
        try {
            header('Content-Type: application/json');
            
            $departments = $this->dashboardModel->departmentStats();

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Department statistics retrieved',
                'data' => $departments
            ]);
        } catch (\Exception $e) {
            error_log("Department stats error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error loading departments',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * GET /api/dashboard/recent-leaves
     */
    public function recentLeaves()
    {
        try {
            header('Content-Type: application/json');
            
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
            $leaves = $this->dashboardModel->recentLeaves($limit);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Recent leaves retrieved',
                'data' => $leaves
            ]);
        } catch (\Exception $e) {
            error_log("Recent leaves error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error loading leave requests',
                'error' => $e->getMessage()
            ]);
        }
    }
}