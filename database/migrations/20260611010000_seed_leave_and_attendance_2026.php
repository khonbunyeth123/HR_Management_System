<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use App\Support\Uuid;

final class SeedLeaveAndAttendance2026 extends AbstractMigration
{
    public function up(): void
    {
        // 1. Fetch all employees
        $employees = $this->fetchAll('SELECT id FROM tbl_employees');
        if (empty($employees)) {
            throw new RuntimeException('No employees found to seed data.');
        }

        // Define 2026 Cambodian Holidays
        $holidays = [
            '2026-01-01', '2026-01-07', '2026-03-08', '2026-04-14', '2026-04-15', '2026-04-16',
            '2026-05-01', '2026-05-05', '2026-05-14', '2026-06-18', '2026-09-24', '2026-10-10',
            '2026-10-11', '2026-10-12', '2026-10-15', '2026-10-29', '2026-11-09', '2026-11-23',
            '2026-11-24', '2026-11-25', '2026-12-29'
        ];

        // 2. Seed Leave Applications (randomized)
        $leaveData = [];
        $createdAt = date('Y-m-d H:i:s');
        
        $minTimestamp = strtotime('2026-01-01');
        $maxTimestamp = strtotime('2026-06-10');
        
        foreach ($employees as $index => $emp) {
            // Randomly decide if an employee has a leave application (e.g., 50% chance)
            if (rand(0, 1)) {
                $randomTimestamp = rand($minTimestamp, $maxTimestamp);
                $startDate = date('Y-m-d', $randomTimestamp);
                $endDate = date('Y-m-d', strtotime($startDate . ' + ' . rand(0, 2) . ' days'));
                
                $leaveData[] = [
                    'uuid'          => Uuid::v4(),
                    'employee_id'   => $emp['id'],
                    'leave_type_id' => rand(1, 3), // Assuming 3 leave types
                    'start_date'    => $startDate,
                    'end_date'      => $endDate,
                    'reason'        => 'Random reason for leave',
                    'status_id'     => rand(0, 2),
                    'created_at'    => $createdAt,
                ];
            }
        }
        if (!empty($leaveData)) {
            $this->table('tbl_leave_applications')->insert($leaveData)->saveData();
        }

        // 3. Seed Attendance Records from 2026-01-01 until 2026-06-10
        $attendanceData = [];
        $startDate = new DateTime('2026-01-01');
        $endDate = new DateTime('2026-06-10');
        $interval = new DateInterval('P1D');
        $period = new DatePeriod($startDate, $interval, $endDate->modify('+1 day'));

        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            
            // Skip Sunday (day of week 0)
            if ($date->format('w') == 0) {
                continue;
            }

            // Skip Holidays
            if (in_array($dateStr, $holidays)) {
                continue;
            }

            foreach ($employees as $emp) {
                // Check-in 1 (08:00)
                $attendanceData[] = [
                    'uuid'          => Uuid::v4(),
                    'employee_id'   => $emp['id'],
                    'date'          => $dateStr,
                    'check_time'    => '08:00:00',
                    'scan_datetime' => $dateStr . ' 08:00:00',
                    'check_type_id' => 1,
                    'status'        => 'On Time',
                    'status_id'     => 1,
                    'created_at'    => $createdAt,
                ];
                // Check-out 1 (12:00)
                $attendanceData[] = [
                    'uuid'          => Uuid::v4(),
                    'employee_id'   => $emp['id'],
                    'date'          => $dateStr,
                    'check_time'    => '12:00:00',
                    'scan_datetime' => $dateStr . ' 12:00:00',
                    'check_type_id' => 2,
                    'status'        => 'On Time',
                    'status_id'     => 1,
                    'created_at'    => $createdAt,
                ];
                // Check-in 2 (13:00)
                $attendanceData[] = [
                    'uuid'          => Uuid::v4(),
                    'employee_id'   => $emp['id'],
                    'date'          => $dateStr,
                    'check_time'    => '13:00:00',
                    'scan_datetime' => $dateStr . ' 13:00:00',
                    'check_type_id' => 3,
                    'status'        => 'On Time',
                    'status_id'     => 1,
                    'created_at'    => $createdAt,
                ];
                // Check-out 2 (17:00)
                $attendanceData[] = [
                    'uuid'          => Uuid::v4(),
                    'employee_id'   => $emp['id'],
                    'date'          => $dateStr,
                    'check_time'    => '17:00:00',
                    'scan_datetime' => $dateStr . ' 17:00:00',
                    'check_type_id' => 4,
                    'status'        => 'On Time',
                    'status_id'     => 1,
                    'created_at'    => $createdAt,
                ];
            }
        }
        $this->table('tbl_attendance_records')->insert($attendanceData)->saveData();
    }

    public function down(): void
    {
        $this->execute("DELETE FROM tbl_leave_applications WHERE start_date >= '2026-01-01'");
        $this->execute("DELETE FROM tbl_attendance_records WHERE date >= '2026-01-01'");
    }
}
