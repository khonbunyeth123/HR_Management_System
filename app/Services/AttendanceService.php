<?php

namespace App\Services;

use App\Models\Attendance;
use Ramsey\Uuid\Uuid;

class AttendanceService
{
    private Attendance $model;

    public function __construct()
    {
        $this->model = new Attendance(); // Initialize the Attendance model
    }

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


     public function scan(int $employeeId): array
    {
        $date = date('Y-m-d');
        $time = date('H:i:s');

        $count = $this->model->getTodayScanCount($employeeId, $date);

        if ($count >= 4) {
            return ['error' => 'Already completed today'];
        }

        $nextCheckType = $count + 1;

        if ($this->model->existsScan($employeeId, $date, $nextCheckType)) {
            return ['error' => 'Already scanned'];
        }

        $checkType = $this->model->getCheckType($nextCheckType);

        if (!$checkType) {
            return ['error' => 'Invalid check type'];
        }
        $this->model->insertScan([
            'uuid' => Uuid::uuid4()->toString(),
            'employee_id' => $employeeId,
            'date' => $date,
            'check_time' => $time,
            'check_type_id' => $nextCheckType
        ]);

        return [
            'success' => true,
            'scan_type' => $nextCheckType,
            'label' => $checkType['name'],
            'time' => $time,
            'standard_time' => $checkType['standard_time']
        ];
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