<?php
declare(strict_types=1);

namespace App\Tests\Unit;

use App\Models\Attendance;
use App\Services\AttendanceService;
use PHPUnit\Framework\TestCase;

class AttendanceServiceTest extends TestCase
{
    public function testLateAttendanceRequiresReason(): void
    {
        $model = $this->createMock(Attendance::class);
        $service = new AttendanceService($model);

        $model->expects($this->once())
            ->method('getDailyAttendanceMap')
            ->with(12, '2026-08-14')
            ->willReturn([
                1 => ['check_type_id' => 1],
            ]);

        $model->expects($this->once())
            ->method('getCheckType')
            ->with(2)
            ->willReturn([
                'name' => 'Check-out 1',
                'standard_time' => '12:00:00',
            ]);

        $model->expects($this->never())
            ->method('insertScan');

        $result = $service->scan(12, '2026-08-14 11:45:00');

        $this->assertArrayHasKey('error', $result);
        $this->assertSame('A reason is required for Early Leave.', $result['error']);
        $this->assertTrue($result['requires_note']);
        $this->assertSame('Early Leave', $result['status']);
        $this->assertSame('Check-out 1', $result['label']);
    }

    public function testOnTimeAttendanceDefaultsNoteToGood(): void
    {
        $model = $this->createMock(Attendance::class);
        $service = new AttendanceService($model);

        $model->expects($this->once())
            ->method('getDailyAttendanceMap')
            ->with(12, '2026-08-14')
            ->willReturn([]);

        $model->expects($this->once())
            ->method('getCheckType')
            ->with(1)
            ->willReturn([
                'name' => 'Check-in 1',
                'standard_time' => '08:00:00',
            ]);

        $model->expects($this->once())
            ->method('insertScan')
            ->with($this->callback(function (array $data): bool {
                return $data['check_type_id'] === 1
                    && $data['status'] === 'On Time'
                    && $data['note'] === 'Good'
                    && $data['check_time'] === '08:00:00';
            }))
            ->willReturn(true);

        $result = $service->scan(12, '2026-08-14 08:00:00');

        $this->assertTrue($result['success']);
        $this->assertSame('On Time', $result['status']);
        $this->assertSame('Good', $result['note']);
    }
}
