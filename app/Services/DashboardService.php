<?php

namespace App\Services;

use App\Models\Dashboard;

class DashboardService
{
    public function __construct(
        private readonly Dashboard $model
    ) {}

    public function summary(): array
    {
        return [
            'total_employees'  => $this->model->totalEmployees(),
            'active_employees' => $this->model->activeEmployees(),
            'pending_leaves'   => $this->model->pendingLeaves(),
            'on_leave_today'   => $this->model->onLeaveToday()
        ];
    }

    public function departmentStats(): array
    {
        return $this->model->departmentStats();
    }

    public function recentLeaves(int $limit = 4): array
    {
        return $this->model->recentLeaves($limit);
    }

    public function calendarEvents(string $month): array
    {
        return $this->model->getCalendarEvents($month);
    }
}
