<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Services\EmployeeService;

class ControllerEmployee
{
    private EmployeeService $service;

    public function __construct()
    {
        $this->service = new EmployeeService();
    }

    // Helper: JSON response for success
    private function jsonSuccess($data = null, string $message = 'Success'): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data
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
        $payload = json_decode(file_get_contents('php://input'), true);
        if (!$payload) $this->jsonError('Invalid JSON payload', 400);

        $this->service->create($payload, $this->authUserId());
        $this->jsonSuccess(null, 'Employee created');
    }

    // Update employee
    public function update(int $id): void
    {
        $payload = json_decode(file_get_contents('php://input'), true);
        if (!$payload) {
            $this->jsonError('Invalid JSON payload', 400);
        }

        $this->service->update($id, $payload);  // <-- use $service, not $employeeService
        $this->jsonSuccess(null, 'Employee updated successfully');
    }

     //Delate employee
    public function delete(int $id): void
    {
        $this->service->delete($id, $this->authUserId());
        $this->jsonSuccess(null, 'Employee deleted');
    }

    /* ---------- JSON RESPONSE ---------- */
    private function sendJson(array $data, int $status = 200): void
    {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);
        exit;
    }


    // Delete employee
    public function destroy(): void
    {
        $payload = json_decode(file_get_contents('php://input'), true);
        if (!$payload || !isset($payload['id'])) {
            $this->jsonError('Missing employee ID', 400);
        }

        $this->service->delete((int)$payload['id'], $this->authUserId());
        $this->jsonSuccess(null, 'Employee deleted');
    }

    // Helper to get current logged-in user
    private function authUserId(): int
    {
        return $_SESSION['user_id'] ?? 1;
    }
}
