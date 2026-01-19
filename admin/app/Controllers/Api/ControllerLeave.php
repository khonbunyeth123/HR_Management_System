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
}
