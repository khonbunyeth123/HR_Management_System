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
        // Handle photo upload if present
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $photoPath = $this->handlePhotoUpload($_FILES['photo']);
            if ($photoPath) {
                $data['photo'] = $photoPath;
            }
        }
 
        // UUID is generated in model if missing.
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = $authUserId;
 
        return $this->employeeModel->create($data);
    }
 
    // Update
    public function update(int $id, array $data, array $files = []): bool
{
    // Merge $_FILES (POST) with manually parsed $files (PUT)
    $fileSource = !empty($files) ? $files : $_FILES;

    if (isset($fileSource['photo']) && $fileSource['photo']['error'] === UPLOAD_ERR_OK) {
        $photoPath = $this->handlePhotoUpload($fileSource['photo']);
        if ($photoPath) {
            $data['photo'] = $photoPath;
        }
    }

    return $this->employeeModel->update($id, $data);
}
 
    // Delete
    public function delete(int $id, int $userId): bool
    {
        return $this->employeeModel->Delete($id, $userId);
    }
 
    // Handle photo upload
    private function handlePhotoUpload(array $file): ?string
    {
        $uploadDir = '../public/uploads/employees/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        // Use finfo instead of $file['type'] — prevents spoofed MIME from client
        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        if (!in_array($mimeType, $allowedTypes)) {
            return null;
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            return null;
        }

        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $fileName      = 'employee_' . uniqid() . '.' . $fileExtension;
        $filePath      = $uploadDir . $fileName;

        // move_uploaded_file() only works for native $_FILES (POST)
        // rename() handles manually parsed PUT temp files
        $moved = move_uploaded_file($file['tmp_name'], $filePath)
            || rename($file['tmp_name'], $filePath);

        return $moved ? 'uploads/employees/' . $fileName : null;
    }
}