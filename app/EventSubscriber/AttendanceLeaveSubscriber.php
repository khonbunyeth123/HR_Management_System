<?php
declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\LeaveApprovedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Models\Attendance;
use App\Core\Database;

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
        
        // Logic to "inject" attendance records if needed.
        // Previously, Attendance model used UNION ALL to fetch approved leaves.
        // If we want to physically inject them into tbl_attendance_records, we do it here.
        // However, the prompt says "move UNION ALL side effects here out of the Attendance model".
        // This implies that instead of dynamic UNION ALL, we might want to materialize 
        // attendance records for the leave period.
        
        $db = Database::getInstance()->getConnection();
        $startDate = new \DateTime($leave['start_date']);
        $endDate = new \DateTime($leave['end_date']);
        $interval = new \DateInterval('P1D');
        $period = new \DatePeriod($startDate, $interval, $endDate->modify('+1 day'));

        foreach ($period as $date) {
            $formattedDate = $date->format('Y-m-d');
            
            // Insert a special attendance record for each day of the leave
            $sql = "INSERT IGNORE INTO tbl_attendance_records (uuid, employee_id, date, check_time, check_type_id, status_id, created_at)
                    VALUES (:uuid, :employee_id, :date, '08:00:00', 5, 1, NOW())"; // type 5 = Leave
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':uuid' => bin2hex(random_bytes(16)),
                ':employee_id' => $leave['employee_id'],
                ':date' => $formattedDate
            ]);
        }
    }
}
