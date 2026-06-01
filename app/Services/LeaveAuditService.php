<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Audit\LeaveAudit;

/**
 * Service for managing leave audit logs.
 */
class LeaveAuditService
{
    private LeaveAudit $model;

    public function __construct()
    {
        $this->model = new LeaveAudit();
    }

    /**
     * Log a created action.
     */
    public function logCreated(int $leaveId, int $userId, ?string $ipAddress = null): void
    {
        $this->model->record($leaveId, 'created', $userId, $ipAddress);
    }

    /**
     * Log an approved action.
     */
    public function logApproved(int $leaveId, int $userId, ?string $ipAddress = null): void
    {
        $this->model->record($leaveId, 'approved', $userId, $ipAddress);
    }

    /**
     * Log a rejected action.
     */
    public function logRejected(int $leaveId, int $userId, ?string $ipAddress = null): void
    {
        $this->model->record($leaveId, 'rejected', $userId, $ipAddress);
    }

    /**
     * Log a cancelled action.
     */
    public function logCancelled(int $leaveId, int $userId, ?string $ipAddress = null): void
    {
        $this->model->record($leaveId, 'cancelled', $userId, $ipAddress);
    }
}
