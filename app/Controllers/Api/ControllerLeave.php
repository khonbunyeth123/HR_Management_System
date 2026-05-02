<?php
declare(strict_types=1);
namespace App\Controllers\Api;
use App\Services\LeaveService;

class ControllerLeave
{
    private LeaveService $service;

    public function __construct()
    {
        $this->service = new LeaveService();
        header("Content-Type: application/json");
    }

    public function index(): void
    {
        $page    = (int)($_GET['paging_options']['page'] ?? 1);
        $perPage = (int)($_GET['paging_options']['per_page'] ?? 5);

        $filters = [
            "employee_name" => $_GET['filters']['employee_name'] ?? '',
            "leave_type"    => $_GET['filters']['leave_type'] ?? '',
            "status_id"     => $_GET['filters']['status_id'] ?? ''
        ];

        $result = $this->service->listLeaves($filters, $page, $perPage);

        echo json_encode([
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


    //create
    public function create(): void
    {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST');
        header('Access-Control-Allow-Headers: Content-Type');

        $input = json_decode(file_get_contents('php://input'), true);

        // Get employee_id from session (token), not from client
        $input['employee_id'] = $_SESSION['employee_id'] ?? null;

        // Convert leave_type string → leave_type_id
        if (!empty($input['leave_type']) && empty($input['leave_type_id'])) {
            $input['leave_type_id'] = $this->service->getLeaveTypeIdByName($input['leave_type']);
        }

        $result = $this->service->create($input);

        if ($result['success']) {
            echo json_encode([
                'success' => true,
                'message' => 'Leave application created successfully',
                'data'    => $result['data'] ?? null
            ]);
        } else {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $result['error'] ?? 'Failed to create leave application'
            ]);
        }
    }

    public function getLeaveTypeIdByName(string $name): ?int
    {
        return $this->model->getLeaveTypeIdByName($name);
    }

    //approve
         public function approve(): void
    {

        $input = json_decode(file_get_contents('php://input'), true);
        $uuid = $input['uuid'] ?? '';

        if (empty($uuid)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'UUID is required'
            ]);
            return;
        }

        $result = $this->service->approveLeave($uuid);

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Leave application approved successfully'
            ]);
        } else {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to approve leave application'
            ]);
        }
    }

    //reject
    public function reject(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);

        $uuid   = $input['uuid'] ?? '';
        $remark = $input['remark'] ?? '';

        if (!$uuid || !$remark) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'UUID and remark are required'
            ]);
            return;
        }

        // ✅ CORRECT METHOD
        $result = $this->service->rejectLeave($uuid, $remark);

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Leave application rejected successfully'
            ]);
        } else {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to reject leave application'
            ]);
        }
    }

    public function history(): void
    {
        $employeeId = (int)($_SESSION['employee_id'] ?? 0);
        if (!$employeeId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $page    = (int)($_GET['page']     ?? 1);
        $perPage = (int)($_GET['per_page'] ?? 20);

        $result = $this->service->getEmployeeHistory($employeeId, $page, $perPage);

        echo json_encode([
            'success' => true,
            'data'    => [
                'leave_applications' => $result['rows'],
                'leave_types'        => $this->service->leaveTypes(),
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
