<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Services\LeaveService;
use App\Security\LeaveVoter;
use App\Enum\LeaveStatus;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for leave API endpoints.
 */
class ControllerLeave extends BaseController
{
    public function __construct(
        private readonly LeaveService $service
    ) {}

    /**
     * List all leave applications.
     */
    #[Route('/api/leaves', name: 'api_leaves_index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $page    = $request->query->getInt('page', 1);
        $perPage = $request->query->getInt('per_page', 5);

        $filters = $request->query->all('filters');
        $result = $this->service->listLeaves($filters, $page, $perPage);

        return $this->json([
            "success" => true,
            "data" => [
                "leave_applications" => $result['rows'],
                "leave_types" => $this->service->leaveTypes()
            ],
            "pagination" => [
                "total" => $result['total'],
                "page" => $page,
                "per_page" => $perPage,
                "total_pages" => $result['pages']
            ]
        ]);
    }

    /**
     * Create a new leave application.
     */
    #[Route('/api/leaves', name: 'api_leaves_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(LeaveVoter::LEAVE_STORE);

        $input = json_decode($request->getContent(), true);
        $input['employee_id'] = $this->getUser()?->id;

        $result = $this->service->create($input);

        if ($result['success']) {
            return $this->json([
                'success' => true,
                'message' => 'Leave application created successfully',
                'data'    => $result['data']
            ]);
        }

        return $this->json([
            'success' => false,
            'message' => $result['error'] ?? 'Failed to create leave application'
        ], 400);
    }

    /**
     * Approve a leave application.
     */
    #[Route('/api/leaves/{uuid}/approve', name: 'api_leaves_approve', methods: ['PATCH'])]
    public function approve(string $uuid): JsonResponse
    {
        $leave = $this->service->getLeaveByUuid($uuid);
        $this->denyAccessUnlessGranted(LeaveVoter::LEAVE_APPROVE, $leave);

        $actorId = (int)$this->getUser()?->id;
        $result = $this->service->approveLeave($uuid, $actorId);

        if ($result) {
            return $this->json([
                'success' => true,
                'message' => 'Leave application approved successfully'
            ]);
        }

        return $this->json([
            'success' => false,
            'message' => 'Failed to approve leave application'
        ], 400);
    }

    /**
     * Reject a leave application.
     */
    #[Route('/api/leaves/{uuid}/reject', name: 'api_leaves_reject', methods: ['PATCH'])]
    public function reject(Request $request, string $uuid): JsonResponse
    {
        $leave = $this->service->getLeaveByUuid($uuid);
        $this->denyAccessUnlessGranted(LeaveVoter::LEAVE_REJECT, $leave);

        $input = json_decode($request->getContent(), true);
        $remark = $input['remark'] ?? '';

        if (!$remark) {
            return $this->json(['success' => false, 'message' => 'Remark is required'], 400);
        }

        $actorId = (int)$this->getUser()?->id;
        $result = $this->service->rejectLeave($uuid, $remark, $actorId);

        if ($result) {
            return $this->json([
                'success' => true,
                'message' => 'Leave application rejected successfully'
            ]);
        }

        return $this->json([
            'success' => false,
            'message' => 'Failed to reject leave application'
        ], 400);
    }

    /**
     * Get employee leave history.
     */
    #[Route('/api/leaves/history', name: 'api_leaves_history', methods: ['GET'])]
    public function history(Request $request): JsonResponse
    {
        $employeeId = (int)$this->getUser()?->id;
        if (!$employeeId) {
            return $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $page    = $request->query->getInt('page', 1);
        $perPage = $request->query->getInt('per_page', 20);

        $result = $this->service->getEmployeeHistory($employeeId, $page, $perPage);

        return $this->json([
            'success' => true,
            'data'    => [
                'leave_applications' => $result['rows'],
                'leave_types'        => [],
            ],
            'pagination' => [
                'total'       => $result['total'],
                'page'        => $page,
                'per_page'    => $perPage,
                'total_pages' => $result['total_pages'],
            ],
        ]);
    }
}
