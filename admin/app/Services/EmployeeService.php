<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Employee;

class EmployeeService
{
    private Employee $employeeModel;

    public function __construct()
    {
        $this->employeeModel = new Employee();
    }

    // List all employees
    public function list(): array
    {
        return $this->employeeModel->getAll();
    }

    // Show single employee
    public function show(int $id): ?array
    {
        return $this->employeeModel->getById($id);
    }

    // Create
    public function create(array $data, int $authUserId): bool
    {
        $data['uuid'] = bin2hex(random_bytes(16));  // generate UUID
        $data['created_at'] = date('Y-m-d H:i:s');  // current datetime
        $data['created_by'] = $authUserId;          // logged-in user id

        return $this->employeeModel->create($data);
    }


    // Update
    public function update(int $id, array $data): bool
    {
        return $this->employeeModel->update($id, $data);
    } 

    // Delete
    public function delete(int $id, int $userId): bool
    {
        return $this->employeeModel->Delete($id, $userId);
    }
}
