<?php
declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\LeaveApprovedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Models\Attendance;
use App\Support\Uuid;

/**
 * Subscriber to handle attendance injection when a leave is approved.
 */
class AttendanceLeaveSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            LeaveApprovedEvent::class => 'onLeaveApproved',
        ];
    }

    public function onLeaveApproved(LeaveApprovedEvent $event): void
    {
        $leave = $event->leave;

        $attendanceModel = new Attendance();
        $leaveCheckTypeId = $attendanceModel->getCheckTypeIdByName('Leave');
        if ($leaveCheckTypeId === null) {
            error_log('Attendance leave injection skipped: leave check type is missing.');
            return;
        }

        $db = \App\Core\Database::getInstance()->getConnection();
        $startDate = new \DateTime($leave['start_date']);
        $endDate = new \DateTime($leave['end_date']);
        $interval = new \DateInterval('P1D');
        $period = new \DatePeriod($startDate, $interval, $endDate->modify('+1 day'));

        foreach ($period as $date) {
            $formattedDate = $date->format('Y-m-d');
            
            // Insert a special attendance record for each day of the leave
            $sql = "INSERT IGNORE INTO tbl_attendance_records (uuid, employee_id, date, scan_datetime, check_time, check_type_id, status, status_id, created_at)
                    VALUES (:uuid, :employee_id, :date, CONCAT(:date_scan, ' 08:00:00'), '08:00:00', :check_type_id, 'On Time', 1, NOW())";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':uuid' => Uuid::v4(),
                ':employee_id' => $leave['employee_id'],
                ':date' => $formattedDate,
                ':date_scan' => $formattedDate,
                ':check_type_id' => $leaveCheckTypeId,
            ]);
        }
    }
}
