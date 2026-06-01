<?php
declare(strict_types=1);

namespace App\Enum;

/**
 * Enum representing the status of a leave application.
 */
enum LeaveStatus: int
{
    case PENDING = 0;
    case APPROVED = 1;
    case REJECTED = 2;

    /**
     * Returns a human-readable label for the status.
     * 
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
        };
    }
}
