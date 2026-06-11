<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Models\Dashboard;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ControllerDashboard extends BaseController
{
    public function __construct(
        private readonly Dashboard $dashboardModel
    ) {}

    /**
     * GET /api/dashboard/summary
     */
    public function summary(): JsonResponse
    {
        try {
            $stats = $this->dashboardModel->getSummaryStats();
            return $this->json([
                'success' => true,
                'message' => 'Dashboard summary retrieved',
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            error_log("Dashboard summary error: " . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Error loading statistics'], 500);
        }
    }

    /**
     * GET /api/dashboard/department
     */
    public function department(): JsonResponse
    {
        try {
            $departments = $this->dashboardModel->departmentStats();
            return $this->json([
                'success' => true,
                'message' => 'Department statistics retrieved',
                'data' => $departments
            ]);
        } catch (\Exception $e) {
            error_log("Department stats error: " . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Error loading departments'], 500);
        }
    }

    /**
     * GET /api/dashboard/recent-leaves
     */
    public function recentLeaves(Request $request): JsonResponse
    {
        try {
            $limit = $request->query->getInt('limit', 5);
            $leaves = $this->dashboardModel->recentLeaves($limit);
            return $this->json([
                'success' => true,
                'message' => 'Recent leaves retrieved',
                'data' => $leaves
            ]);
        } catch (\Exception $e) {
            error_log("Recent leaves error: " . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Error loading leave requests'], 500);
        }
    }

    /**
     * GET /api/dashboard/calendar-events
     */
    public function calendarEvents(Request $request): JsonResponse
    {
        try {
            $month = $request->query->get('month', date('Y-m'));
            $employeeId = (int) ($_SESSION['employee_id'] ?? 0);
            $events = $this->dashboardModel->getCalendarEvents($month, $employeeId);
            return $this->json([
                'success' => true,
                'message' => 'Calendar events retrieved',
                'data' => $events
            ]);
        } catch (\Exception $e) {
            error_log("Calendar events error: " . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Error loading calendar events'], 500);
        }
    }
}
