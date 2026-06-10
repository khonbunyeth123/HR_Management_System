<?php

namespace App\Services;

use App\Models\Report;
use DateInterval;
use DatePeriod;
use DateTimeImmutable;

class ReportService
{
    protected $model;

    public function __construct()
    {
        $this->model = new Report();
    }

    public function getDailyList(string $date): array
    {
        $rows = $this->model->fetchDailyAttendance($date);

        $result = [];

        foreach ($rows as $row) {
            $status = $this->buildStatus($row);

            $result[] = [
                'employee_id' => $row['employee_id'],
                'name'        => $row['full_name'],
                'check_in_1'  => $this->formatScan($row['check_in_1'] ?? null),
                'check_in_1_note' => $row['check_in_1_status'] ?? $this->buildPunchNote($row['check_in_1'] ?? null, '08:00:00', true),
                'check_out_1' => $this->formatScan($row['check_out_1'] ?? null),
                'check_out_1_note' => $row['check_out_1_status'] ?? $this->buildPunchNote($row['check_out_1'] ?? null, '12:00:00', false),
                'check_in_2'  => $this->formatScan($row['check_in_2'] ?? null),
                'check_in_2_note' => $row['check_in_2_status'] ?? $this->buildPunchNote($row['check_in_2'] ?? null, '13:00:00', true),
                'check_out_2' => $this->formatScan($row['check_out_2'] ?? null),
                'check_out_2_note' => $row['check_out_2_status'] ?? $this->buildPunchNote($row['check_out_2'] ?? null, '17:00:00', false),
                'status'      => $status
            ];
        }

        return $result;
    }

    private function formatScan(?string $value): string
    {
        if (!$value) {
            return '--:--';
        }

        $ts = strtotime($value);
        return $ts === false ? '--:--' : date('H:i:s', $ts);
    }

    private function buildPunchNote(?string $actualTime, ?string $standardTime, bool $isCheckIn): string
    {
        if (!$actualTime) {
            return 'No record';
        }

        if ($isCheckIn) {
            return strtotime($actualTime) > strtotime($standardTime) ? 'Late' : 'On Time';
        }

        $actualTs = strtotime($actualTime);
        $standardTs = strtotime($standardTime);
        if ($actualTs === false || $standardTs === false) {
            return 'Recorded';
        }
        if ($actualTs < $standardTs) {
            return 'Early Leave';
        }
        if ($actualTs > $standardTs) {
            return 'Overtime';
        }
        return 'On Time';
    }

    private function buildStatus(array $row): string
    {
        $c1 = $row['check_in_1'] ?? null;
        $o1 = $row['check_out_1'] ?? null;
        $c2 = $row['check_in_2'] ?? null;
        $o2 = $row['check_out_2'] ?? null;
        $c1Status = $row['check_in_1_status'] ?? null;
        $o1Status = $row['check_out_1_status'] ?? null;
        $c2Status = $row['check_in_2_status'] ?? null;
        $o2Status = $row['check_out_2_status'] ?? null;

        if (!$c1 && !$o1 && !$c2 && !$o2) {
            return 'Absent';
        }

        if (!$c1 || !$o1 || !$c2 || !$o2) {
            return 'Missing Punch';
        }

        if ($c1Status === 'Late' || $c2Status === 'Late') {
            return 'Late';
        }

        if ($o1Status === 'Early Leave' || $o2Status === 'Early Leave') {
            return 'Early Leave';
        }

        if ($o2Status === 'Overtime') {
            return 'Overtime';
        }

        return 'On Time';
    }

    public function getSummary(string $from, string $to, ?string $department = null): array
    {
        $data = $this->getAttendanceData($from, $to, $department);
        $summary = [];

        foreach ($data as $employee) {
            $totalDays = count($employee['days']);
            $present = 0;
            $late = 0;
            $leave = 0;
            $absent = 0;
            $holiday = 0;
            $dayOff = 0;

            foreach ($employee['days'] as $day) {
                switch ($day['status']) {
                    case 'Present':
                    case 'Late':
                    case 'Missing Checkout':
                    case 'Overtime':
                        $present++;
                        if ($day['isLate']) {
                            $late++;
                        }
                        break;
                    case 'Leave':
                        $leave++;
                        break;
                    case 'Absent':
                        $absent++;
                        break;
                    case 'Public Holiday':
                        $holiday++;
                        break;
                    case 'Day Off':
                        $dayOff++;
                        break;
                }
            }

            $workdays = $present + $absent;
            $summary[] = [
                'id' => $employee['employee_id'],
                'full_name' => $employee['name'],
                'department' => $employee['department'],
                'total_days' => $totalDays,
                'present_days' => $present,
                'late_days' => $late,
                'leave_days' => $leave,
                'absent_days' => $absent,
                'holiday_days' => $holiday,
                'day_off_days' => $dayOff,
                'attendance_percent' => $workdays > 0 ? round(($present / $workdays) * 100, 2) : 0
            ];
        }

        return $summary;
    }

    public function getDetailedAttendance(string $from, string $to, ?string $department = null, ?string $search = null, ?string $status = null): array
    {
        $result = $this->getAttendanceData($from, $to, $department, $search);

        if ($status) {
            foreach ($result as &$employee) {
                $employee['days'] = array_filter($employee['days'], function ($day) use ($status) {
                    return $day['status'] === $status;
                });
            }
        }

        return $result;
    }

    private function getAttendanceData(string $from, string $to, ?string $department = null, ?string $search = null): array
    {
        $rows = $this->model->fetchAttendanceDailyRows($from, $to, $department, $search);
        $leaves = $this->model->fetchApprovedLeaves($from, $to);
        $holidays = $this->model->fetchPublicHolidays($from, $to);
        $dayOffDays = $this->getDayOffDays();

        $leaveMap = [];
        foreach ($leaves as $leave) {
            $employeeId = (int)($leave['employee_id'] ?? 0);
            $start = new DateTimeImmutable((string)$leave['start_date']);
            $end = new DateTimeImmutable((string)$leave['end_date']);
            $period = new DatePeriod($start, new DateInterval('P1D'), $end->modify('+1 day'));

            foreach ($period as $date) {
                $leaveMap[$employeeId][$date->format('Y-m-d')] = (string)($leave['leave_type'] ?? 'Leave');
            }
        }

        $holidayMap = [];
        foreach ($holidays as $holiday) {
            $holidayMap[(string)($holiday['holiday_date'] ?? '')] = (string)($holiday['title'] ?? 'Public Holiday');
        }

        $grouped = [];
        foreach ($rows as $row) {
            $employeeId = (int)$row['employee_id'];
            $date = (string)($row['date'] ?? '');
            if ($date === '') {
                continue;
            }

            if (!isset($grouped[$employeeId])) {
                $grouped[$employeeId] = [
                    'employee_id' => $employeeId,
                    'name' => (string)$row['full_name'],
                    'department' => (string)$row['department'],
                    'days' => [],
                ];
            }

            if (!isset($grouped[$employeeId]['days'][$date])) {
                $grouped[$employeeId]['days'][$date] = [
                    'date' => $date,
                    'day' => (string)($row['day_name'] ?? date('l', strtotime($date))),
                    'c1' => '--:--', 'c1Note' => 'No record',
                    'o1' => '--:--', 'o1Note' => 'No record',
                    'c2' => '--:--', 'c2Note' => 'No record',
                    'o2' => '--:--', 'o2Note' => 'No record',
                    'status' => 'Absent',
                    'isLate' => false,
                    'overtime' => null,
                ];
            }

            $day = &$grouped[$employeeId]['days'][$date];
            $checkType = (string)($row['check_type'] ?? '');
            $time = $this->formatScan($row['scan_datetime'] ?? null);
            $rawStatus = (string)($row['status'] ?? 'Recorded');

            if ($checkType === 'Check-in 1') {
                $day['c1'] = $time;
                $day['c1Note'] = $rawStatus;
                if ($rawStatus === 'Late') {
                    $day['isLate'] = true;
                }
            } elseif ($checkType === 'Check-out 1') {
                $day['o1'] = $time;
                $day['o1Note'] = $rawStatus;
            } elseif ($checkType === 'Check-in 2') {
                $day['c2'] = $time;
                $day['c2Note'] = $rawStatus;
                if ($rawStatus === 'Late') {
                    $day['isLate'] = true;
                }
            } elseif ($checkType === 'Check-out 2') {
                $day['o2'] = $time;
                $day['o2Note'] = $rawStatus;
                if ($rawStatus === 'Overtime') {
                    $day['overtime'] = $time;
                }
            }

            unset($day);
        }

        $dates = $this->buildDateRange($from, $to);
        $result = [];

        foreach ($grouped as $employee) {
            foreach ($dates as $date) {
                if (!isset($employee['days'][$date])) {
                    $employee['days'][$date] = [
                        'date' => $date,
                        'day' => date('l', strtotime($date)),
                        'c1' => '--:--', 'c1Note' => 'No record',
                        'o1' => '--:--', 'o1Note' => 'No record',
                        'c2' => '--:--', 'c2Note' => 'No record',
                        'o2' => '--:--', 'o2Note' => 'No record',
                        'status' => 'Absent',
                        'isLate' => false,
                        'overtime' => null,
                    ];
                }

                $day = $employee['days'][$date];
                $day['status'] = $this->resolveFinalStatus(
                    $employee['employee_id'],
                    $date,
                    $day,
                    $leaveMap,
                    $holidayMap,
                    $dayOffDays
                );
                $employee['days'][$date] = $day;
            }

            $result[] = $employee;
        }

        return $result;
    }

    private function buildDateRange(string $from, string $to): array
    {
        $dates = [];
        $cursor = new DateTimeImmutable($from);
        $end = new DateTimeImmutable($to);
        while ($cursor <= $end) {
            $dates[] = $cursor->format('Y-m-d');
            $cursor = $cursor->add(new DateInterval('P1D'));
        }
        return $dates;
    }

    private function getDayOffDays(): array
    {
        $config = require __DIR__ . '/../../config/attendance_config.php';
        $days = $config['day_off_days'] ?? ['Sunday'];
        return array_map(static fn ($day) => strtolower(trim((string) $day)), $days);
    }

    private function resolveFinalStatus(int $employeeId, string $date, array $day, array $leaveMap, array $holidayMap, array $dayOffDays): string
    {
        if (isset($holidayMap[$date])) {
            return 'Public Holiday';
        }

        if (isset($leaveMap[$employeeId][$date])) {
            return 'Leave';
        }

        $weekday = strtolower(date('l', strtotime($date)));
        if (in_array($weekday, $dayOffDays, true)) {
            return 'Day Off';
        }

        $hasAnyPunch = ($day['c1'] !== '--:--') || ($day['o1'] !== '--:--') || ($day['c2'] !== '--:--') || ($day['o2'] !== '--:--');
        if (!$hasAnyPunch) {
            return 'Absent';
        }

        if ($day['c1'] !== '--:--' && $day['o1'] === '--:--' && $day['c2'] === '--:--' && $day['o2'] === '--:--') {
            return 'Missing Checkout';
        }

        if ($day['isLate']) {
            return 'Late';
        }

        return 'Present';
    }
    public function getTopEmployees(string $from, string $to): array
    {
        $data = $this->getAttendanceData($from, $to);
        $top = [];

        foreach ($data as $employee) {
            $present = 0;
            $late = 0;
            $absent = 0;
            $leave = 0;
            $dayOff = 0;
            $totalDays = count($employee['days']);

            foreach ($employee['days'] as $day) {
                switch ($day['status']) {
                    case 'Present':
                    case 'Late':
                    case 'Missing Checkout':
                    case 'Overtime':
                        $present++;
                        if ($day['isLate']) {
                            $late++;
                        }
                        break;
                    case 'Leave':
                        $leave++;
                        break;
                    case 'Absent':
                        $absent++;
                        break;
                    case 'Day Off':
                        $dayOff++;
                        break;
                }
            }

            $workdays = $present + $absent;
            $top[] = [
                'id' => $employee['employee_id'],
                'full_name' => $employee['name'],
                'department' => $employee['department'],
                'present_days' => $present,
                'late_days' => $late,
                'leave_days' => $leave,
                'day_off_days' => $dayOff,
                'total_days' => $totalDays,
                'absent_days' => $absent,
                'attendance_percent' => $workdays > 0 ? round(($present / $workdays) * 100, 1) : 0
            ];
        }

        // Sort by present_days DESC, late_days ASC
        usort($top, function ($a, $b) {
            if ($a['present_days'] !== $b['present_days']) {
                return $b['present_days'] <=> $a['present_days'];
            }
            return $a['late_days'] <=> $b['late_days'];
        });

        return $top;
    }
}
