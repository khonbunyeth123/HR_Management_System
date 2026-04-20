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

}
