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
        if (!empty($_POST)) {
            $payload = $_POST;
        } else {
            $payload = json_decode(file_get_contents('php://input'), true);
            if ($payload === null || json_last_error() !== JSON_ERROR_NONE) {
                $this->jsonError('Invalid payload - expected form data or JSON', 400);
                return;
            }
        }

        $this->service->create($payload, $this->authUserId());
        $this->jsonSuccess(null, 'Employee created');
    }

    // Update employee
    public function update(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            [$payload, $files] = $this->parsePutMultipart();
        } elseif (!empty($_POST)) {
            $payload = $_POST;
            $files   = $_FILES;
        } else {
            $payload = json_decode(file_get_contents('php://input'), true);
            if ($payload === null || json_last_error() !== JSON_ERROR_NONE) {
                $this->jsonError('Invalid payload', 400);
                return;
            }
            $files = [];
        }

        if (empty($payload)) {
            $this->jsonError('Payload cannot be empty', 400);
            return;
        }

        $this->service->update($id, $payload, $files);
        $this->jsonSuccess(null, 'Employee updated successfully');
    }

    // Parse multipart/form-data for PUT requests
    // PHP only populates $_POST and $_FILES for POST — not PUT.
    // This method manually parses the raw body and returns [$fields, $files].
    private function parsePutMultipart(): array
    {
        $payload = [];
        $files   = [];
        $rawBody = file_get_contents('php://input');
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        // Extract boundary (handle optional quotes and trailing whitespace)
        if (!preg_match('/boundary=("?)([^";\s]+)\1/', $contentType, $matches)) {
            return [$payload, $files];
        }

        $boundary = $matches[2];
        $parts    = array_slice(explode('--' . $boundary, $rawBody), 1);

        foreach ($parts as $part) {
            // End of multipart
            if ($part === '--' || trim($part) === '--') break;

            // Must have header/body separator
            if (!str_contains($part, "\r\n\r\n")) continue;

            [$headers, $body] = explode("\r\n\r\n", $part, 2);
            $body = rtrim($body, "\r\n");

            // Must have a field name
            if (!preg_match('/name="([^"]+)"/', $headers, $nameMatch)) continue;
            $name = $nameMatch[1];

            // File field
            if (preg_match('/filename="([^"]*)"/', $headers, $fileMatch)) {
                $filename = $fileMatch[1];
                if ($filename === '') continue; // no file selected

                // Save raw binary to a temp file
                $tmpPath = tempnam(sys_get_temp_dir(), 'upload_');
                file_put_contents($tmpPath, $body);

                // Detect real MIME type from content (not client header)
                $finfo    = new \finfo(FILEINFO_MIME_TYPE);
                $mimeType = $finfo->file($tmpPath);

                $files[$name] = [
                    'name'     => $filename,
                    'type'     => $mimeType,
                    'tmp_name' => $tmpPath,
                    'error'    => UPLOAD_ERR_OK,
                    'size'     => strlen($body),
                ];
            } else {
                // Regular text field
                $payload[$name] = $body;
            }
        }

        return [$payload, $files];
    }

    // Delete employee
    public function delete(int $id): void
    {
        $this->service->delete($id, $this->authUserId());
        $this->jsonSuccess(null, 'Employee deleted');
    }

    // Delete employee (by body payload)
    public function destroy(): void
    {
        $payload = json_decode(file_get_contents('php://input'), true);
        if (!$payload || !isset($payload['id'])) {
            $this->jsonError('Missing employee ID', 400);
        }

        $this->service->delete((int)$payload['id'], $this->authUserId());
        $this->jsonSuccess(null, 'Employee deleted');
    }

    /* ---------- helpers ---------- */

    private function sendJson(array $data, int $status = 200): void
    {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);
        exit;
    }

    private function authUserId(): int
    {
        return $_SESSION['user_id'] ?? 1;
    }
}