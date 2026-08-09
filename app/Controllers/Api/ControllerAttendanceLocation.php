<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Helpers\Response;
use App\Services\AttendanceLocationService;

class ControllerAttendanceLocation
{
    public function __construct(
        private readonly AttendanceLocationService $service
    ) {}

    public function index(): void
    {
        try {
            Response::json([
                'success' => true,
                'message' => 'Attendance locations retrieved',
                'data' => [
                    'locations' => $this->service->listLocations(),
                    'current_location' => $this->service->getCurrentLocation(),
                ],
            ]);
        } catch (\RuntimeException $e) {
            Response::error($e->getMessage(), 503);
        }
    }

    public function show(int $id): void
    {
        try {
            $location = $this->service->getLocation($id);
            if (!$location) {
                Response::notFound('Attendance location not found');
                return;
            }

            Response::json([
                'success' => true,
                'message' => 'Attendance location retrieved',
                'data' => $location,
            ]);
        } catch (\RuntimeException $e) {
            Response::error($e->getMessage(), 503);
        }
    }

    public function store(): void
    {
        try {
            $input = $this->readJsonBody();
            $validation = $this->service->validateLocationPayload($input, false);

            if (!$validation['valid']) {
                Response::validationError($validation['errors']);
                return;
            }

            $actorId = (int) ($_SESSION['user_id'] ?? 0);
            $result = $this->service->createLocation($validation['data'], $actorId);

            Response::created([
                'id' => $result['id'],
                'location' => $result['location'],
            ], 'Attendance location created successfully');
        } catch (\RuntimeException $e) {
            Response::error($e->getMessage(), 503);
        }
    }

    public function update(int $id): void
    {
        try {
            $input = $this->readJsonBody();
            $validation = $this->service->validateLocationPayload($input, true);

            if (!$validation['valid']) {
                Response::validationError($validation['errors']);
                return;
            }

            $actorId = (int) ($_SESSION['user_id'] ?? 0);
            $result = $this->service->updateLocation($id, $validation['data'], $actorId);

            if (!$result) {
                Response::notFound('Attendance location not found');
                return;
            }

            Response::json([
                'success' => true,
                'message' => 'Attendance location updated successfully',
                'data' => $result['location'],
            ]);
        } catch (\RuntimeException $e) {
            Response::error($e->getMessage(), 503);
        }
    }

    public function destroy(int $id): void
    {
        try {
            $ok = $this->service->deactivateLocation($id, (int) ($_SESSION['user_id'] ?? 0));

            if (!$ok) {
                Response::notFound('Attendance location not found');
                return;
            }

            Response::json([
                'success' => true,
                'message' => 'Attendance location deactivated successfully',
            ]);
        } catch (\RuntimeException $e) {
            Response::error($e->getMessage(), 503);
        }
    }

    private function readJsonBody(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === false || trim($raw) === '') {
            return [];
        }

        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
}
