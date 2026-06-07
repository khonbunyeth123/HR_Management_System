<?php

namespace App\Services;

use App\Models\Report;

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
                'check_in_1'  => $row['check_in_1'] ?? '--:--',
                'check_in_1_note' => $this->buildPunchNote(
                    $row['check_in_1'] ?? null,
                    $row['check_in_1_standard_time'] ?? null,
                    true
                ),
                'check_out_1' => $row['check_out_1'] ?? '--:--',
                'check_out_1_note' => $this->buildPunchNote(
                    $row['check_out_1'] ?? null,
                    $row['check_out_1_standard_time'] ?? null,
                    false
                ),
                'check_in_2'  => $row['check_in_2'] ?? '--:--',
                'check_in_2_note' => $this->buildPunchNote(
                    $row['check_in_2'] ?? null,
                    $row['check_in_2_standard_time'] ?? null,
                    true
                ),
                'check_out_2' => $row['check_out_2'] ?? '--:--',
                'check_out_2_note' => $this->buildPunchNote(
                    $row['check_out_2'] ?? null,
                    $row['check_out_2_standard_time'] ?? null,
                    false
                ),
                'status'      => $status
            ];
        }

        return $result;
    }

    private function buildPunchNote(?string $actualTime, ?string $standardTime, bool $isCheckIn): string
    {
        if (!$actualTime) {
            return 'No record';
        }

        if (!$standardTime) {
            return 'Recorded';
        }

        $actualTs   = strtotime($actualTime);
        $standardTs = strtotime($standardTime);

        if ($actualTs === false || $standardTs === false) {
            return 'Recorded';
        }

        if ($isCheckIn) {
            return $actualTs <= $standardTs ? 'On Time' : 'Late';
        }

        return $actualTs >= $standardTs ? 'On Time' : 'Early';
    }

    private function buildStatus(array $row): string
    {
        $c1 = $row['check_in_1'] ?? null;
        $o1 = $row['check_out_1'] ?? null;
        $c2 = $row['check_in_2'] ?? null;
        $o2 = $row['check_out_2'] ?? null;

        if (!$c1 && !$o1 && !$c2 && !$o2) {
            return 'Absent';
        }

        if (!$c1 || !$o1 || !$c2 || !$o2) {
            return 'Incomplete';
        }

        // Late if check_in_1 > 08:00 or check_in_2 > 13:00
        if ($c1 > '08:00:00' || $c2 > '13:00:00') {
            return 'Late';
        }

        return 'On Time';
    }

    public function getSummary(string $from, string $to, ?string $department = null): array
    {
        return $this->model->summary($from, $to, $department);
    }

    public function getDetailedAttendance(string $from,string $to,?string $department = null,?string $search = null,?string $status = null): array 
    {
        return $this->model->fetchDetailedAttendance(
            $from, $to, $department, $search, $status
        );
    }
    public function getTopEmployees(string $from, string $to): array
    {
        return $this->model->fetchTopEmployees($from, $to);
    }
}
