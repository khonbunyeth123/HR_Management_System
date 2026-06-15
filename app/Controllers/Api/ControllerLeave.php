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
        $pagingOptions = $request->query->all('paging_options');
        if (!is_array($pagingOptions)) {
            $pagingOptions = [];
        }

        $page = $request->query->getInt(
            'page',
            (int) ($pagingOptions['page'] ?? 1)
        );
        $perPage = $request->query->getInt(
            'per_page',
            (int) ($pagingOptions['per_page'] ?? 5)
        );

        $page = max(1, $page);
        $perPage = max(1, min(50, $perPage));

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

        $input = $this->normalizeCreateInput($this->readInput($request));
        if ($input === []) {
            return $this->json([
                'success' => false,
                'message' => 'Invalid request body. Send JSON or form data.'
            ], 422);
        }

        // Ensure employee_id is set
        if (empty($input['employee_id'])) {
            $user = $this->getUser();
            if ($user && isset($user->id)) {
                $input['employee_id'] = (int) $user->id;
            } else {
                return $this->json(['success' => false, 'message' => 'Employee ID is required and could not be determined.'], 400);
            }
        } else {
            $input['employee_id'] = (int) $input['employee_id'];
        }

        // Ensure leave_type_id is set, resolve from name if needed
        if (empty($input['leave_type_id'])) {
            $leaveTypeName = trim((string) ($input['leave_type'] ?? $input['leave_type_name'] ?? ''));
            if ($leaveTypeName !== '') {
                $id = $this->service->getLeaveTypeIdByName($leaveTypeName);
                if ($id) {
                    $input['leave_type_id'] = $id;
                }
            }
        } else {
            $input['leave_type_id'] = (int) $input['leave_type_id'];
        }

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

    private function normalizeCreateInput(array $input): array
    {
        if ($input === []) {
            return [];
        }

        $aliases = [
            'leave_type_id' => ['leave_type_id', 'leaveTypeId', 'leave_type', 'leaveType', 'type_id', 'typeId'],
            'start_date'    => ['start_date', 'startDate', 'start', 'from_date', 'fromDate', 'date_from'],
            'end_date'      => ['end_date', 'endDate', 'end', 'to_date', 'toDate', 'date_to'],
            'reason'        => ['reason', 'remark', 'description', 'details', 'note'],
            'employee_id'   => ['employee_id', 'employeeId'],
        ];

        $normalized = $input;

        foreach ($aliases as $target => $candidates) {
            if (isset($normalized[$target]) && $normalized[$target] !== '') {
                continue;
            }

            foreach ($candidates as $candidate) {
                if (!array_key_exists($candidate, $normalized)) {
                    continue;
                }

                $value = $normalized[$candidate];
                if ($value === null || $value === '') {
                    continue;
                }

                if ($target === 'leave_type_id' && !is_numeric($value)) {
                    continue;
                }

                $normalized[$target] = $value;
                break;
            }
        }

        if (isset($normalized['leave_type_id']) && !is_numeric($normalized['leave_type_id'])) {
            $normalized['leave_type'] = (string) $normalized['leave_type_id'];
            unset($normalized['leave_type_id']);
        }

        if (isset($normalized['reason'])) {
            $normalized['reason'] = trim((string) $normalized['reason']);
        }

        if (isset($normalized['start_date'])) {
            $normalized['start_date'] = trim((string) $normalized['start_date']);
        }

        if (isset($normalized['end_date'])) {
            $normalized['end_date'] = trim((string) $normalized['end_date']);
        }

        return $normalized;
    }

    private function readInput(Request $request): array
    {
        $formData = $request->request->all();
        if (is_array($formData) && !empty($formData)) {
            return $formData;
        }

        $content = trim((string) $request->getContent());
        if ($content === '') {
            return [];
        }

        $json = json_decode($content, true);
        if (is_array($json)) {
            return $json;
        }

        $parsed = [];
        parse_str($content, $parsed);
        return is_array($parsed) ? $parsed : [];
    }

    /**
     * Approve a leave application.
     */
    #[Route('/api/leaves/{uuid}/approve', name: 'api_leaves_approve', methods: ['PATCH'])]
    public function approve(Request $request, ?string $uuid = null): JsonResponse
    {
        $input = $this->readInput($request);
        $uuid = trim((string) ($uuid ?? ($input['uuid'] ?? '')));

        if ($uuid === '') {
            return $this->json([
                'success' => false,
                'message' => 'UUID is required'
            ], 422);
        }

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
    public function reject(Request $request, ?string $uuid = null): JsonResponse
    {
        $input = $this->readInput($request);
        $uuid = trim((string) ($uuid ?? ($input['uuid'] ?? '')));

        if ($uuid === '') {
            return $this->json([
                'success' => false,
                'message' => 'UUID is required'
            ], 422);
        }

        $leave = $this->service->getLeaveByUuid($uuid);
        $this->denyAccessUnlessGranted(LeaveVoter::LEAVE_REJECT, $leave);

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

    #[Route('/api/leaves/{uuid}/reopen', name: 'api_leaves_reopen', methods: ['PATCH'])]
    public function reopen(Request $request, ?string $uuid = null): JsonResponse
    {
        $uuid = trim((string) ($uuid ?? ''));
        if ($uuid === '') {
            return $this->json(['success' => false, 'message' => 'UUID is required'], 422);
        }

        $leave = $this->service->getLeaveByUuid($uuid);
        $this->denyAccessUnlessGranted(LeaveVoter::LEAVE_REJECT, $leave);

        $actorId = (int)$this->getUser()?->id;
        $result = $this->service->reopenLeave($uuid, $actorId);

        if ($result) {
            return $this->json(['success' => true, 'message' => 'Leave application reopened successfully']);
        }

        return $this->json(['success' => false, 'message' => 'Failed to reopen leave application'], 400);
    }

    #[Route('/api/leaves/{uuid}/cancel-approval', name: 'api_leaves_cancel_approval', methods: ['PATCH'])]
    public function cancelApproval(Request $request, ?string $uuid = null): JsonResponse
    {
        $uuid = trim((string) ($uuid ?? ''));
        if ($uuid === '') {
            return $this->json(['success' => false, 'message' => 'UUID is required'], 422);
        }

        $leave = $this->service->getLeaveByUuid($uuid);
        $this->denyAccessUnlessGranted(LeaveVoter::LEAVE_APPROVE, $leave);

        $actorId = (int)$this->getUser()?->id;
        $result = $this->service->cancelApproval($uuid, $actorId);

        if ($result) {
            return $this->json(['success' => true, 'message' => 'Leave approval cancelled successfully']);
        }

        return $this->json(['success' => false, 'message' => 'Failed to cancel leave approval'], 400);
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
