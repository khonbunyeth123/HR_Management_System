<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\AttendanceLocation;

class AttendanceLocationService
{
    public function __construct(
        private readonly AttendanceLocation $model
    ) {}

    public function listLocations(): array
    {
        return $this->model->list();
    }

    public function getCurrentLocation(): ?array
    {
        return $this->model->getCurrentLocation();
    }

    public function getLocation(int $id): ?array
    {
        return $this->model->getById($id);
    }

    public function validateLocationPayload(array $input, bool $isUpdate = false): array
    {
        $errors = [];
        $data = [];

        $hasName = array_key_exists('name', $input);
        $hasLatitude = array_key_exists('latitude', $input);
        $hasLongitude = array_key_exists('longitude', $input);
        $hasRadius = array_key_exists('radius', $input);
        $hasStatus = array_key_exists('status', $input);

        if (!$isUpdate || $hasName) {
            $name = trim((string) ($input['name'] ?? ''));
            if ($name === '') {
                $errors['name'] = 'Location name is required.';
            } elseif (mb_strlen($name) > 255) {
                $errors['name'] = 'Location name must not exceed 255 characters.';
            } else {
                $data['name'] = $name;
            }
        }

        if (!$isUpdate || $hasLatitude) {
            $latitudeRaw = trim((string) ($input['latitude'] ?? ''));
            if ($latitudeRaw === '') {
                $errors['latitude'] = 'Latitude is required.';
            } elseif (!is_numeric($latitudeRaw)) {
                $errors['latitude'] = 'Latitude must be a valid number.';
            } else {
                $latitude = (float) $latitudeRaw;
                if ($latitude < -90 || $latitude > 90) {
                    $errors['latitude'] = 'Latitude must be between -90 and 90.';
                } else {
                    $data['latitude'] = number_format($latitude, 8, '.', '');
                }
            }
        }

        if (!$isUpdate || $hasLongitude) {
            $longitudeRaw = trim((string) ($input['longitude'] ?? ''));
            if ($longitudeRaw === '') {
                $errors['longitude'] = 'Longitude is required.';
            } elseif (!is_numeric($longitudeRaw)) {
                $errors['longitude'] = 'Longitude must be a valid number.';
            } else {
                $longitude = (float) $longitudeRaw;
                if ($longitude < -180 || $longitude > 180) {
                    $errors['longitude'] = 'Longitude must be between -180 and 180.';
                } else {
                    $data['longitude'] = number_format($longitude, 8, '.', '');
                }
            }
        }

        if (!$isUpdate || $hasRadius) {
            $radiusRaw = trim((string) ($input['radius'] ?? ''));
            if ($radiusRaw === '') {
                $errors['radius'] = 'Allowed radius is required.';
            } elseif (filter_var($radiusRaw, FILTER_VALIDATE_INT) === false) {
                $errors['radius'] = 'Allowed radius must be an integer.';
            } else {
                $radius = (int) $radiusRaw;
                if ($radius <= 0) {
                    $errors['radius'] = 'Allowed radius must be greater than 0.';
                } else {
                    $data['radius'] = $radius;
                }
            }
        }

        if (!$isUpdate || $hasStatus) {
            $status = strtolower(trim((string) ($input['status'] ?? '')));
            if ($status === '') {
                $errors['status'] = 'Status is required.';
            } elseif (!in_array($status, ['active', 'inactive'], true)) {
                $errors['status'] = 'Status must be active or inactive.';
            } else {
                $data['status'] = $status;
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'data' => $data,
        ];
    }

    public function createLocation(array $data, int $actorId = 0): array
    {
        $id = $this->model->create($data);

        if (($data['status'] ?? 'inactive') === 'active') {
            $this->model->activate($id);
        }

        $location = $this->model->getById($id);
        return [
            'id' => $id,
            'location' => $location,
            'actor_id' => $actorId,
        ];
    }

    public function updateLocation(int $id, array $data, int $actorId = 0): ?array
    {
        $existing = $this->model->getById($id);
        if (!$existing) {
            return null;
        }

        $ok = $this->model->update($id, $data);
        if (!$ok) {
            return null;
        }

        $nextStatus = strtolower((string) ($data['status'] ?? $existing['status'] ?? 'inactive'));
        if ($nextStatus === 'active') {
            $this->model->activate($id);
        }

        return [
            'id' => $id,
            'location' => $this->model->getById($id),
            'actor_id' => $actorId,
        ];
    }

    public function deactivateLocation(int $id, int $actorId = 0): bool
    {
        $location = $this->model->getById($id);
        if (!$location) {
            return false;
        }

        return $this->model->deactivate($id);
    }
}
