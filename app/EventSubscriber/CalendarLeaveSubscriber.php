<?php
declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\LeaveApprovedEvent;
use App\Event\LeaveRejectedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Models\CalendarEvent;

/**
 * Subscriber to sync leave applications with the calendar.
 */
class CalendarLeaveSubscriber implements EventSubscriberInterface
{
    private CalendarEvent $calendarModel;

    public function __construct()
    {
        $this->calendarModel = new CalendarEvent();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LeaveApprovedEvent::class => 'onLeaveApproved',
            LeaveRejectedEvent::class => 'onLeaveRejected',
        ];
    }

    public function onLeaveApproved(LeaveApprovedEvent $event): void
    {
        $leave = $event->leave;
        
        // Sync to calendar
        $this->calendarModel->create([
            'title' => 'Leave: ' . ($leave['employee_name'] ?? 'Employee'),
            'description' => $leave['reason'],
            'event_type' => 'leave',
            'status' => 'approved',
            'start' => $leave['start_date'] . ' 00:00:00',
            'end' => $leave['end_date'] . ' 23:59:59',
            'all_day' => 1,
            'targets' => [
                ['type' => 'employee', 'value' => (string)$leave['employee_id']]
            ]
        ]);
    }

    public function onLeaveRejected(LeaveRejectedEvent $event): void
    {
        $leave = $event->leave;
        
        // If it was previously in the calendar (e.g. as pending), update it.
        // For now, we just ensure it's not marked as approved if it was somehow there.
        // Typically, we might search for an existing event by meta and delete/update it.
    }
}
