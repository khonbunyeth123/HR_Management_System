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
                'check_out_1' => $row['check_out_1'] ?? '--:--',
                'check_in_2'  => $row['check_in_2'] ?? '--:--',
                'check_out_2' => $row['check_out_2'] ?? '--:--',
                'status'      => $status
            ];
        }

        return $result;
    }

    private function buildStatus(array $row): string
    {
        if (empty($row['check_in_1'])) return 'Absent';
        if ($row['check_in_1'] > '08:00') return 'Late';
        if (empty($row['check_out_1']) || empty($row['check_out_2'])) return 'Incomplete';
        return 'On Time';
    }

    public function getSummary(string $from, string $to, ?string $department = null): array
    {
        return $this->model->summary($from, $to, $department);
    }
}