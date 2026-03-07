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

    public function approveLeave(string $uuid): bool
    {
        return $this->model->approveLeave($uuid, 1, null); // 1 = approved
    }

    public function rejectLeave(string $uuid, string $remark): bool
    {
        return $this->model->rejectLeave($uuid, $remark);
    }


    public function create(array $input): array
    {
        // Required fields
        $required = ['employee_id', 'leave_type_id', 'start_date', 'end_date', 'reason'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                return ['success' => false, 'error' => "Missing required field: $field"];
            }
        }

        // Validate dates
        if ($input['end_date'] < $input['start_date']) {
            return ['success' => false, 'error' => "End date cannot be before start date"];
        }

        // Optional: business rules (e.g., max leave days, overlapping leaves)

        // Delegate to model
        return $this->model->create(
            (int)$input['employee_id'],
            (int)$input['leave_type_id'],
            $input['start_date'],
            $input['end_date'],
            $input['reason']
        );
    }

}