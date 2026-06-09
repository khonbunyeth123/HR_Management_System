<?php
declare(strict_types=1);

namespace App\Repository;

use App\Enum\LeaveStatus;

/**
 * Interface for Leave repository operations.
 */
interface LeaveRepositoryInterface
{
    /**
     * Find all pending leave applications.
     * 
     * @return array
     */
    public function findPending(): array;

    /**
     * Find leave applications by employee ID.
     * 
     * @param int $employeeId
     * @return array
     */
    public function findByEmployee(int $employeeId): array;

    /**
     * Find a leave application by UUID.
     * 
     * @param string $uuid
     * @return array|null
     */
    public function findByUuid(string $uuid): ?array;

    /**
     * Approve a leave application.
     * 
     * @param int $id
     * @param int $approvedBy
     * @return bool
     */
    public function approve(int $id, int $approvedBy): bool;

    /**
     * Reject a leave application.
     * 
     * @param int $id
     * @param int $rejectedBy
     * @param string $reason
     * @return bool
     */
    public function reject(int $id, int $rejectedBy, string $reason): bool;

    /**
     * Reopen a rejected leave application.
     */
    public function reopen(int $id, int $actorId): bool;

    /**
     * Cancel an approved leave application.
     */
    public function cancelApproval(int $id, int $actorId): bool;

    /**
     * Create a new leave application.
     * 
     * @param array $data
     * @return array
     */
    public function create(array $data): array;

    /**
     * Find all leave applications with filters and pagination.
     */
    public function listAll(array $filters, int $page, int $perPage): array;

    /**
     * Get all leave types.
     */
    public function getLeaveTypes(): array;

    /**
     * Get a leave type ID by name.
     */
    public function getLeaveTypeIdByName(string $name): ?int;
}
