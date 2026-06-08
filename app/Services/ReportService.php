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
