<?php
declare(strict_types=1);

namespace App\Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\LeaveService;
use App\Repository\LeaveRepositoryInterface;
use App\Event\LeaveApprovedEvent;
use App\Event\LeaveRejectedEvent;
use App\Services\LeaveAuditService;
use App\Services\NotificationService;
use App\Enum\LeaveStatus;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use LogicException;

/**
 * Unit tests for LeaveService.
 */
class LeaveServiceTest extends TestCase
{
    private $repository;
    private $eventDispatcher;
    private $auditService;
    private $notificationService;
    private $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(LeaveRepositoryInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->auditService = $this->createMock(LeaveAuditService::class);
        $this->notificationService = $this->createMock(NotificationService::class);
        
        $this->service = new LeaveService(
            $this->repository,
            $this->eventDispatcher,
            $this->auditService,
            $this->notificationService
        );
    }

    public function testApproveDispatchesEvent(): void
    {
        $uuid = 'test-uuid';
        $leave = ['id' => 1, 'uuid' => $uuid, 'status_id' => LeaveStatus::PENDING->value, 'employee_id' => 123];
        
        $this->repository->method('findByUuid')->willReturn($leave);
        $this->repository->expects($this->once())
            ->method('approve')
            ->with(1, 1)
            ->willReturn(true);
        
        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(LeaveApprovedEvent::class));
            
        $this->service->approveLeave($uuid, 1);
    }

    public function testRejectDispatchesEvent(): void
    {
        $uuid = 'test-uuid';
        $leave = ['id' => 1, 'uuid' => $uuid, 'status_id' => LeaveStatus::PENDING->value, 'employee_id' => 123];
        
        $this->repository->method('findByUuid')->willReturn($leave);
        $this->repository->expects($this->once())
            ->method('reject')
            ->with(1, 1, 'Reason')
            ->willReturn(true);
        
        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(LeaveRejectedEvent::class));
            
        $this->service->rejectLeave($uuid, 'Reason', 1);
    }

    public function testApprovingAlreadyApprovedThrowsException(): void
    {
        $uuid = 'test-uuid';
        $leave = ['id' => 1, 'uuid' => $uuid, 'status_id' => LeaveStatus::APPROVED->value, 'employee_id' => 123];
        
        $this->repository->method('findByUuid')->willReturn($leave);
        
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Leave is already approved.');
        
        $this->service->approveLeave($uuid, 1);
    }
}
