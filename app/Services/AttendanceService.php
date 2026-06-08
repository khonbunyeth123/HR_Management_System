<?php

namespace App\Services;

use App\Models\Attendance;
use App\Support\Uuid;

class AttendanceService
{
    private const WINDOWS = [
        1 => ['label' => 'Check-in 1',  'start' => '07:00:00', 'end' => '11:59:59'],
        2 => ['label' => 'Check-out 1', 'start' => '12:00:00', 'end' => '12:59:59'],
        3 => ['label' => 'Check-in 2',  'start' => '13:00:00', 'end' => '16:59:59'],
        4 => ['label' => 'Check-out 2', 'start' => '17:00:00', 'end' => '23:59:59'],
    ];

    public function __construct(
        private readonly Attendance $model
    ) {}

    public function getPaginatedRecords(int $page, int $perPage, array $filters): array
    {
        $offset = ($page - 1) * $perPage;
        $statusId = null;
        foreach ($filters as $filter) {
            if ($filter['field'] === 'status_id') {
                $statusId = $filter['value'];
            }
        }
        $records = $this->model->getList($perPage, $offset, $statusId);
        $total = $this->model->countAll($statusId);

        return [
            'records' => $records,
            'total' => $total
        ];
    }


    public function scan(int $employeeId, ?string $scanAt = null): array
    {
        $scanDateTime = $scanAt ?: date('Y-m-d H:i:s');
        $scan = new \DateTimeImmutable($scanDateTime);
        $date = $scan->format('Y-m-d');
        $time = $scan->format('H:i:s');

        $resolved = $this->resolveAttendanceType($employeeId, $scan);
        if (isset($resolved['error'])) {
            return $resolved;
        }

        $checkType = $this->model->getCheckType((int) $resolved['check_type_id']);
        if (!$checkType) {
            return ['error' => 'Invalid check type'];
        }

        $status = $resolved['status'];

        $this->model->insertScan([
            'uuid' => Uuid::v4(),
            'employee_id' => $employeeId,
            'date' => $date,
            'scan_datetime' => $scan->format('Y-m-d H:i:s'),
            'check_time' => $time,
            'check_type_id' => (int) $resolved['check_type_id'],
            'status' => $status,
        ]);

        return [
            'success' => true,
            'scan_type' => (int) $resolved['check_type_id'],
            'label' => $checkType['name'],
            'time' => $time,
            'standard_time' => $checkType['standard_time']
        ];
    }

    private function resolveAttendanceType(int $employeeId, \DateTimeImmutable $scan): array
    {
        $date = $scan->format('Y-m-d');
        $time = $scan->format('H:i:s');
        $existing = $this->model->getDailyAttendanceMap($employeeId, $date);

        foreach ([1, 2, 3, 4] as $typeId) {
            if (isset($existing[$typeId])) {
                continue;
            }

            if (!$this->isWithinWindow($time, self::WINDOWS[$typeId]['start'], self::WINDOWS[$typeId]['end'])) {
                continue;
            }

            return [
                'check_type_id' => $typeId,
                'status' => $this->resolveStatus($typeId, $time),
            ];
        }

        if (!isset($existing[1]) && $this->compareTime($time, '08:00:00') < 0) {
            return ['check_type_id' => 1, 'status' => 'On Time'];
        }

        if (!isset($existing[1]) && $this->compareTime($time, '08:00:00') > 0 && $this->compareTime($time, '11:59:59') <= 0) {
            return ['check_type_id' => 1, 'status' => 'Late'];
        }

        if (!isset($existing[2]) && $this->compareTime($time, '12:00:00') < 0) {
            return ['check_type_id' => 2, 'status' => 'Early Leave'];
        }

        if (!isset($existing[3]) && $this->compareTime($time, '13:00:00') <= 0) {
            return ['check_type_id' => 3, 'status' => 'On Time'];
        }

        if (!isset($existing[3]) && $this->compareTime($time, '13:00:00') > 0 && $this->compareTime($time, '16:59:59') <= 0) {
            return ['check_type_id' => 3, 'status' => 'Late'];
        }

        if (!isset($existing[4]) && $this->compareTime($time, '17:00:00') < 0) {
            return ['check_type_id' => 4, 'status' => 'Early Leave'];
        }

        if (!isset($existing[4]) && $this->compareTime($time, '17:00:00') === 0) {
            return ['check_type_id' => 4, 'status' => 'On Time'];
        }

        if (!isset($existing[4]) && $this->compareTime($time, '17:00:00') > 0) {
            return ['check_type_id' => 4, 'status' => 'Overtime'];
        }

        return ['error' => 'No valid attendance window for this scan'];
    }

    private function resolveStatus(int $checkTypeId, string $time): string
    {
        return match ($checkTypeId) {
            1 => $this->compareTime($time, '08:00:00') <= 0 ? 'On Time' : 'Late',
            2 => $this->compareTime($time, '12:00:00') < 0 ? 'Early Leave' : 'On Time',
            3 => $this->compareTime($time, '13:00:00') <= 0 ? 'On Time' : 'Late',
            4 => $this->compareTime($time, '17:00:00') < 0 ? 'Early Leave' : ($this->compareTime($time, '17:00:00') === 0 ? 'On Time' : 'Overtime'),
            default => 'Recorded',
        };
    }

    private function isWithinWindow(string $time, string $start, string $end): bool
    {
        return $this->compareTime($time, $start) >= 0 && $this->compareTime($time, $end) <= 0;
    }

    private function compareTime(string $left, string $right): int
    {
        return strcmp($left, $right);
    }

    public function getCheckinPageData(): array
    {
        return [
            'employees' => $this->model->getActiveEmployees(),
            'slot'      => $this->model->getSlotByHour(),
        ];
    }
    public function checkin(int $employeeId): array
    {
        error_log("=== CHECKIN DEBUG === employee_id: " . $employeeId);

        $slot = $this->model->getSlotByHour();
        error_log("SLOT: " . json_encode($slot));

        if ($slot['slot'] === 0) {
            return ['error' => 'Attendance is only allowed during office hours.', 'type' => 'warning'];
        }

        $result = $this->scan($employeeId);
        error_log("SCAN RESULT: " . json_encode($result));

        if (isset($result['error'])) {
            return ['error' => $result['error'], 'type' => 'warning'];
        }

        return [
            'success' => true,
            'message' => $result['label'] . ' recorded at ' . $result['time'],
            'type'    => 'success'
        ];
    }

    public function getHistory(int $employeeId, int $page, int $perPage): array
    {
        $offset  = ($page - 1) * $perPage;
        $records = $this->model->getByEmployeeId($employeeId, $perPage, $offset);
        $total   = $this->model->countByEmployeeId($employeeId);

        return [
            'records'     => $records,
            'total'       => $total,
            'total_pages' => (int)ceil($total / $perPage),
        ];
    }
} 
