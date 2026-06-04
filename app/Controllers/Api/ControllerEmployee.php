<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Services\EmployeeService;

class ControllerEmployee
{
    public function __construct(
        private readonly EmployeeService $service
    ) {}

    // Helper: JSON response for success
    private function jsonSuccess($data = null, string $message = 'Success'): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data'    => $data
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    // Helper: JSON response for error
    private function jsonError(string $message, int $status = 400): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => $message
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    // List all employees
    public function index(): void
    {
        $employees = $this->service->list();
        $this->jsonSuccess($employees);
    }

    public function departments(): void
    {
        $depts = $this->service->getDepartments();
        $this->jsonSuccess($depts);
    }

    // Show single employee by ID
    public function show(int $id): void
    {
        $employee = $this->service->show($id);
        if (!$employee) {
            $this->jsonError("Employee not found", 404);
        }
        $this->jsonSuccess($employee);
    }

    // Create employee
    public function store(): void
    {
        $data = $_POST;
        $authUserId = (int) ($_SESSION['user_id'] ?? 0);

        if ($this->service->create($data, $authUserId)) {
            $this->jsonSuccess(null, "Employee created successfully");
        } else {
            $this->jsonError("Failed to create employee");
        }
    }

    // Update employee
    public function update(int $id): void
    {
        // For PUT requests, we need to parse the input manually
        // because PHP doesn't populate $_POST for PUT.
        $data = [];
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            // Read raw input
            $rawInput = file_get_contents('php://input');
            
            // If it's multipart/form-data (required for files)
            // we'd need a more complex parser. 
            // For simple JSON or x-www-form-urlencoded:
            parse_str($rawInput, $data);
            
            // If it's JSON:
            if (empty($data)) {
                $data = json_decode($rawInput, true) ?? [];
            }
        } else {
            $data = $_POST;
        }

        if ($this->service->update($id, $data)) {
            $this->jsonSuccess(null, "Employee updated successfully");
        } else {
            $this->jsonError("Failed to update employee");
        }
    }

    // Delete employee
    public function delete(int $id): void
    {
        $authUserId = (int) ($_SESSION['user_id'] ?? 0);
        if ($this->service->delete($id, $authUserId)) {
            $this->jsonSuccess(null, "Employee deleted successfully");
        } else {
            $this->jsonError("Failed to delete employee");
        }
    }

    public function calendarEvents(): void
    {
        $month = $_GET['month'] ?? date('Y-m');
        $employeeId = (int) ($_GET['employee_id'] ?? $_SESSION['employee_id'] ?? 0);

        if (!$employeeId) {
            $this->jsonError('Employee ID required', 400);
        }

        $events = $this->service->getCalendarEvents($month, $employeeId);
        $this->jsonSuccess($events);
    }
}
