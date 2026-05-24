<?php
namespace App\Services;
use App\Models\Leave;
class LeaveService
{
    private Leave $model;
    private NotificationService $notificationService;
    public function __construct()
    {
        $this->model = new Leave();
        $this->notificationService = new NotificationService();
    }
    public function listLeaves(array $filters, int $page, int $perPage): array
    {
        if ($page < 1) $page = 1;
        if ($perPage > 50) $perPage = 50;
        $result = $this->model->getAll($filters, $page, $perPage);
        return ['total' => $result['total'], 'rows' => $result['rows'], 'pages' => ceil($result['total'] / $perPage)];
    }
    public function leaveTypes(): array { return $this->model->getLeaveTypes(); }
    public function approveLeave(string $uuid): bool
    {
        $ok = $this->model->approveLeave($uuid, 1, null);
        if ($ok) {
            $employeeId = $this->model->getEmployeeIdByUuid($uuid);
            if ($employeeId) $this->notificationService->sendLeaveApproved($employeeId);
        }
        return $ok;
    }
    public function rejectLeave(string $uuid, string $remark): bool
    {
        $ok = $this->model->rejectLeave($uuid, $remark);
        if ($ok) {
            $employeeId = $this->model->getEmployeeIdByUuid($uuid);
            if ($employeeId) $this->notificationService->sendLeaveRejected($employeeId, $remark);
        }
        return $ok;
    }
    public function create(array $input): array
    {
        $required = ['employee_id', 'leave_type_id', 'start_date', 'end_date', 'reason'];
        foreach ($required as $field) {
            if (empty($input[$field])) return ['success' => false, 'error' => "Missing required field: $field"];
        }
        if ($input['end_date'] < $input['start_date']) return ['success' => false, 'error' => 'End date cannot be before start date'];
        return $this->model->create((int)$input['employee_id'], (int)$input['leave_type_id'], $input['start_date'], $input['end_date'], $input['reason']);
    }
    public function getLeaveTypeIdByName(string $name): ?int { return $this->model->getLeaveTypeIdByName($name); }
    public function getEmployeeHistory(int $employeeId, int $page, int $perPage): array
    {
        if ($page < 1) $page = 1;
        $offset = ($page - 1) * $perPage;
        $result = $this->model->getByEmployeeId(['employee_id' => $employeeId], $perPage, $offset);
        return ['rows' => $result['rows'], 'total' => $result['total'], 'total_pages' => (int)ceil($result['total'] / $perPage)];
    }
}
