
<?php

namespace App\Controllers\Api;

use App\Services\DashboardService;
use App\Helpers\Response;

class DashboardController
{
    private DashboardService $service;

    public function __construct()
    {
        $this->service = new DashboardService();
    }

    public function summary(): void
    {
        Response::json([
            'success' => true,
            'data' => $this->service->summary()
        ]);
    }

    public function department(): void
    {
        Response::json([
            'success' => true,
            'data' => $this->service->departmentStats()
        ]);
    }

    public function recentLeaves(): void
    {
        Response::json([
            'success' => true,
            'data' => $this->service->recentLeaves(4)
        ]);
    }
}
