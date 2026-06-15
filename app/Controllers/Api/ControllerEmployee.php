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

    // Show single employee by ID or UUID
    public function show($id): void
    {
        $employee = $this->service->show($id);
        
        if (!$employee) {
            $this->jsonError("Employee not found", 404);
        }

        // Clean up data for the mobile app (ensure no nulls for core fields)
        $data = [
            'id'            => (int)    ($employee['id'] ?? 0),
            'uuid'          => (string) ($employee['uuid'] ?? ''),
            'username'      => (string) ($employee['username'] ?? ''),
            'first_name'    => (string) ($employee['first_name'] ?? ''),
            'last_name'     => (string) ($employee['last_name'] ?? ''),
            'full_name'     => (string) ($employee['full_name'] ?? ''),
            'gender'        => (string) ($employee['gender'] ?? ''),
            'email'         => (string) ($employee['email'] ?? ''),
            'phone'         => (string) ($employee['phone'] ?? ''),
            'address'       => (string) ($employee['address'] ?? ''),
            'dob'           => (string) ($employee['dob'] ?? ''),
            'role'          => (string) ($employee['role'] ?? $employee['position'] ?? ''),
            'position'      => (string) ($employee['position'] ?? ''),
            'department'    => (string) ($employee['department'] ?? ''),
            'date_hired'    => (string) ($employee['date_hired'] ?? ''),
            'status_id'     => (int)    ($employee['status_id'] ?? 1),
            'photo'         => (string) ($employee['photo'] ?? ''),
        ];

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'data'    => $data
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    // Create employee
    public function store(): void
    {
        try {
            error_log("DEBUG: ControllerEmployee::store POST=" . json_encode($_POST));
            error_log("DEBUG: ControllerEmployee::store FILES=" . json_encode($_FILES));
            
            $data = $_POST;
            $authUserId = (int) ($_SESSION['user_id'] ?? 0);

            if ($this->service->create($data, $authUserId)) {
                $this->jsonSuccess(null, "Employee created successfully");
            } else {
                $this->jsonError("Failed to create employee");
            }
        } catch (\Exception $e) {
            error_log("ERROR: ControllerEmployee::store Exception=" . $e->getMessage());
            $this->jsonError("Error: " . $e->getMessage());
        }
    }

    // Update employee
    public function update(int $id): void
    {
        try {
            error_log("DEBUG: ControllerEmployee::update id=$id POST=" . json_encode($_POST));
            error_log("DEBUG: ControllerEmployee::update id=$id FILES=" . json_encode($_FILES));
            
            // PHP automatically populates $_POST and $_FILES for multipart/form-data
            // regardless of whether the method is POST or PUT, provided the
            // content-type is set correctly.
            $data = $_POST;
            $authUserId = (int) ($_SESSION['user_id'] ?? 0);

            if ($this->service->update($id, $data, $authUserId)) {
                $this->jsonSuccess(null, "Employee updated successfully");
            } else {
                $this->jsonError("Failed to update employee");
            }
        } catch (\Exception $e) {
            error_log("ERROR: ControllerEmployee::update id=$id Exception=" . $e->getMessage());
            $this->jsonError("Error: " . $e->getMessage());
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
        $authType = $_SESSION['auth_type'] ?? '';
        
        if ($authType === 'employee') {
            $employeeId = (int) $_SESSION['employee_id'];
        } elseif ($authType === 'user') {
            $employeeId = (int) ($_GET['employee_id'] ?? 0);
        } else {
            $this->jsonError('Unauthorized', 403);
        }

        if (!$employeeId && $authType !== 'user') {
            $this->jsonError('Employee ID required', 400);
        }

        $events = $this->service->getCalendarEvents($month, $employeeId);
        $this->jsonSuccess($events);
    }
}
