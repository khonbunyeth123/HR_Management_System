<?php
declare(strict_types=1);

namespace App\Tests\Unit;

use App\Services\NotificationService;
use PHPUnit\Framework\TestCase;

class NotificationServiceTest extends TestCase
{
    public function testCalendarNotificationsDeduplicateSharedFcmTokens(): void
    {
        $service = new class extends NotificationService {
            public array $sentTokens = [];

            protected function getFcmToken(int $employeeId): ?string
            {
                return match ($employeeId) {
                    1, 2 => 'shared-token',
                    3 => 'unique-token',
                    default => null,
                };
            }

            protected function send(string $fcmToken, array $notification, array $data = []): void
            {
                $this->sentTokens[] = [
                    'token' => $fcmToken,
                    'notification' => $notification,
                    'data' => $data,
                ];
            }
        };

        $service->sendCalendarEventNotifications(
            [1, 2, 3, 3, 0],
            'New calendar event',
            'Created: Team meeting',
            ['event_uuid' => 'abc-123']
        );

        $this->assertCount(2, $service->sentTokens);
        $this->assertSame(['shared-token', 'unique-token'], array_column($service->sentTokens, 'token'));
        $this->assertSame('calendar_event', $service->sentTokens[0]['data']['type']);
        $this->assertSame('abc-123', $service->sentTokens[0]['data']['event_uuid']);
    }
}
