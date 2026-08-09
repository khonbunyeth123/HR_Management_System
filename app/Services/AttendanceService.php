<?php

namespace App\Services;

use App\Models\Attendance;
use App\Support\Uuid;

class AttendanceService
{
    /**
     * Scan windows used to decide WHICH check type ("slot") a scan belongs to.
     * These only decide slot assignment, not lateness/earliness — that comes
     * from standardTimeFor() below. Type 3's standard reuses WINDOWS[3]['start']
     * so the second check-in standard only needs to be changed in one place.
     */
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

    /**
     * @param string|null $note Deprecated / ignored. Status and Note are now always
     *                          derived automatically from the scan time by this service
     *                          (single source of truth). Kept in the signature only for
     *                          backward compatibility with existing callers — any value
     *                          passed here is not persisted.
     */
    public function scan(int $employeeId, ?string $scanAt = null, ?string $note = null): array
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

        $status   = $resolved['status'];
        $autoNote = $resolved['note'];

        $this->model->insertScan([
            'uuid' => Uuid::v4(),
            'employee_id' => $employeeId,
            'date' => $date,
            'scan_datetime' => $scan->format('Y-m-d H:i:s'),
            'check_time' => $time,
            'check_type_id' => (int) $resolved['check_type_id'],
            'status' => $status,
            'note' => $autoNote,
        ]);

        return [
            'success' => true,
            'scan_type' => (int) $resolved['check_type_id'],
            'label' => $checkType['name'],
            'time' => $time,
            'standard_time' => $checkType['standard_time'],
            'status' => $status,
            'note' => $autoNote,
        ];
    }

    private function resolveAttendanceType(int $employeeId, \DateTimeImmutable $scan): array
    {
        $date = $scan->format('Y-m-d');
        $time = $scan->format('H:i:s');
        $existing = $this->model->getDailyAttendanceMap($employeeId, $date);

        // Primary path: assign the scan to the first pending check type whose window it falls in.
        foreach ([1, 2, 3, 4] as $typeId) {
            if (isset($existing[$typeId])) {
                continue;
            }

            if (!$this->isWithinWindow($time, self::WINDOWS[$typeId]['start'], self::WINDOWS[$typeId]['end'])) {
                continue;
            }

            return $this->buildTypeResult($typeId, $time);
        }

        // Fallback path: scan arrived outside every configured window (e.g. before 07:00).
        // Assign it to the next pending check type in sequence so the scan is never lost.
        foreach ([1, 2, 3, 4] as $typeId) {
            if (isset($existing[$typeId])) {
                continue;
            }

            return $this->buildTypeResult($typeId, $time);
        }

        return ['error' => 'No valid attendance window for this scan'];
    }

    private function buildTypeResult(int $typeId, string $time): array
    {
        $status = $this->resolveStatus($typeId, $time);

        return [
            'check_type_id' => $typeId,
            'status' => $status,
            'note' => $this->computeNote($typeId, $time, $status),
        ];
    }

    /**
     * Standard time each check type is measured against.
     * Type 3 (second check-in) reuses WINDOWS[3]['start'] so the configured
     * standard only needs to be changed in one place.
     */
    private function standardTimeFor(int $checkTypeId): string
    {
        return match ($checkTypeId) {
            1 => '08:00:00',
            2 => '12:00:00',
            3 => self::WINDOWS[3]['start'],
            4 => '17:00:00',
            default => '00:00:00',
        };
    }

    private function resolveStatus(int $checkTypeId, string $time): string
    {
        $standard = $this->standardTimeFor($checkTypeId);

        return match ($checkTypeId) {
            // Check-in 1: On Time at/before standard, Late after.
            1 => $this->compareTime($time, $standard) <= 0 ? 'On Time' : 'Late',
            // Check-out 1: On Time at/after standard, Early Leave before.
            2 => $this->compareTime($time, $standard) < 0 ? 'Early Leave' : 'On Time',
            // Check-in 2: On Time at/after standard, Late before (per configured rule).
            3 => $this->compareTime($time, $standard) >= 0 ? 'On Time' : 'Late',
            // Check-out 2: On Time at/after standard, Early Leave before.
            4 => $this->compareTime($time, $standard) < 0 ? 'Early Leave' : 'On Time',
            default => 'Recorded',
        };
    }

    /**
     * Automatically generates the Note for a scan based on its status.
     * Formats: "Good", "Late X min", "Early X min".
     */
    private function computeNote(int $checkTypeId, string $time, string $status): string
    {
        if ($status === 'On Time') {
            return 'Good';
        }

        $standard = $this->standardTimeFor($checkTypeId);
        $minutes = $this->diffInMinutes($time, $standard);

        return match ($status) {
            'Late' => "Late {$minutes} min",
            'Early Leave' => "Early {$minutes} min",
            default => $status,
        };
    }

    private function diffInMinutes(string $timeA, string $timeB): int
    {
        return (int) round(abs($this->timeToSeconds($timeA) - $this->timeToSeconds($timeB)) / 60);
    }

    private function timeToSeconds(string $time): int
    {
        [$h, $m, $s] = array_map('intval', explode(':', $time));
        return ($h * 3600) + ($m * 60) + $s;
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

    public function getHistory(int $employeeId, int $page, int $perPage, ?int $month = null, ?int $year = null): array
    {
        $offset  = ($page - 1) * $perPage;
        $records = $this->model->getByEmployeeIdFiltered($employeeId, $perPage, $offset, $month, $year);
        $total   = $this->model->countByEmployeeIdFiltered($employeeId, $month, $year);

        return [
            'records'     => $records,
            'total'       => $total,
            'total_pages' => (int)ceil($total / $perPage),
        ];
    }
}