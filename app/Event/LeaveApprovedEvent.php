<?php
declare(strict_types=1);

namespace App\Event;

use App\Models\Leave;

/**
 * Event dispatched when a leave application is approved.
 */
class LeaveApprovedEvent
{
    /**
     * @param array $leave The leave entity data
     */
    public function __construct(
        public readonly array $leave
    ) {}
}
