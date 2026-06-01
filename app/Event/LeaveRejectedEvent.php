<?php
declare(strict_types=1);

namespace App\Event;

use App\Models\Leave;

/**
 * Event dispatched when a leave application is rejected.
 */
class LeaveRejectedEvent
{
    /**
     * @param array $leave The leave entity data
     * @param string $remark The rejection reason
     */
    public function __construct(
        public readonly array $leave,
        public readonly string $remark
    ) {}
}
