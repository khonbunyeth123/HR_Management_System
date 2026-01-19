<?php

namespace App\Services;

use App\Models\Leave;
class LeaveService
{
    private Leave $model;

    public function __construct()
    {
        $this->model = new Leave(); // Initialize the Leave model
    }

    public function listLeaves(array $filters, int $page, int $perPage): array
    {
        if ($page < 1) $page = 1;
        if ($perPage > 50) $perPage = 50; // limit abuse

        $result = $this->model->getAll($filters, $page, $perPage);

        return [
            "total" => $result['total'],
            "rows"  => $result['rows'],
            "pages" => ceil($result['total'] / $perPage)
        ];
    }

    public function leaveTypes(): array
    {
        return $this->model->getLeaveTypes();
    }
}