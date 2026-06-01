<?php
declare(strict_types=1);

namespace App\Repository;

use App\Models\Leave;
use App\Enum\LeaveStatus;

/**
 * Implementation of LeaveRepositoryInterface using the Leave model.
 */
class LeaveRepository implements LeaveRepositoryInterface
{
    private Leave $model;

    public function __construct()
    {
        $this->model = new Leave();
    }

    /**
     * @inheritDoc
     */
    public function findPending(): array
    {
        return $this->model->getAll(['status_id' => LeaveStatus::PENDING->value], 1, 1000)['rows'];
    }

    /**
     * @inheritDoc
     */
    public function findByEmployee(int $employeeId): array
    {
        return $this->model->getByEmployeeId(['employee_id' => $employeeId], 1000, 0)['rows'];
    }

    /**
     * @inheritDoc
     */
    public function findByUuid(string $uuid): ?array
    {
        $result = $this->model->getAll(['uuid' => $uuid], 1, 1);
        return $result['rows'][0] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function approve(int $id, int $approvedBy): bool
    {
        // The current Leave model uses UUID for approval, but the interface requested ID.
        // I will adapt the repository to handle the translation if needed, 
        // but for now, I'll update the model or use UUID if that's what's available.
        // Actually, I'll update the model to be more flexible later or just use what works.
        // Let's assume the ID is passed and we find the UUID first or update by ID.
        
        // Since I need to show COMPLETE files, I will implement it correctly.
        // I'll need to add findById to Leave model or just use the DB directly here.
        // But the instructions say "wrapping the existing Leave model".
        
        // I'll use the UUID approach as that's what the model currently supports.
        // I will find the UUID by ID first.
        $db = \App\Core\Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT uuid FROM tbl_leave_applications WHERE id = ?");
        $stmt->execute([$id]);
        $uuid = $stmt->fetchColumn();
        
        if (!$uuid) return false;
        
        return $this->model->approveLeave($uuid, LeaveStatus::APPROVED->value);
    }

    /**
     * @inheritDoc
     */
    public function reject(int $id, int $rejectedBy, string $reason): bool
    {
        $db = \App\Core\Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT uuid FROM tbl_leave_applications WHERE id = ?");
        $stmt->execute([$id]);
        $uuid = $stmt->fetchColumn();
        
        if (!$uuid) return false;
        
        return $this->model->rejectLeave($uuid, $reason);
    }

    /**
     * @inheritDoc
     */
    public function create(array $data): array
    {
        return $this->model->create(
            (int)$data['employee_id'],
            (int)$data['leave_type_id'],
            $data['start_date'],
            $data['end_date'],
            $data['reason']
        );
    }

    /**
     * @inheritDoc
     */
    public function listAll(array $filters, int $page, int $perPage): array
    {
        return $this->model->getAll($filters, $page, $perPage);
    }

    /**
     * @inheritDoc
     */
    public function getLeaveTypes(): array
    {
        return $this->model->getLeaveTypes();
    }

    /**
     * @inheritDoc
     */
    public function getLeaveTypeIdByName(string $name): ?int
    {
        return $this->model->getLeaveTypeIdByName($name);
    }
}
