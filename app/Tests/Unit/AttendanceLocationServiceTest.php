<?php
declare(strict_types=1);

namespace App\Tests\Unit;

use App\Models\AttendanceLocation;
use App\Services\AttendanceLocationService;
use PHPUnit\Framework\TestCase;

class AttendanceLocationServiceTest extends TestCase
{
    public function testValidateLocationPayloadNormalizesNumericValues(): void
    {
        $service = new AttendanceLocationService($this->createMock(AttendanceLocation::class));

        $result = $service->validateLocationPayload([
            'name' => 'Sangkat Srah Chak',
            'latitude' => '11.580914',
            'longitude' => '104.909832',
            'radius' => '100',
            'status' => 'ACTIVE',
        ]);

        $this->assertTrue($result['valid']);
        $this->assertSame('Sangkat Srah Chak', $result['data']['name']);
        $this->assertSame('11.58091400', $result['data']['latitude']);
        $this->assertSame('104.90983200', $result['data']['longitude']);
        $this->assertSame(100, $result['data']['radius']);
        $this->assertSame('active', $result['data']['status']);
    }

    public function testValidateLocationPayloadRejectsOutOfRangeCoordinates(): void
    {
        $service = new AttendanceLocationService($this->createMock(AttendanceLocation::class));

        $result = $service->validateLocationPayload([
            'name' => '',
            'latitude' => '120',
            'longitude' => '-200',
            'radius' => '0',
            'status' => 'enabled',
        ]);

        $this->assertFalse($result['valid']);
        $this->assertArrayHasKey('name', $result['errors']);
        $this->assertArrayHasKey('latitude', $result['errors']);
        $this->assertArrayHasKey('longitude', $result['errors']);
        $this->assertArrayHasKey('radius', $result['errors']);
        $this->assertArrayHasKey('status', $result['errors']);
    }

    public function testCreateLocationActivatesNewActiveLocation(): void
    {
        $model = $this->createMock(AttendanceLocation::class);
        $service = new AttendanceLocationService($model);

        $model->expects($this->once())
            ->method('create')
            ->with([
                'name' => 'Sangkat Srah Chak',
                'latitude' => '11.58091400',
                'longitude' => '104.90983200',
                'radius' => 100,
                'status' => 'active',
            ])
            ->willReturn(7);

        $model->expects($this->once())
            ->method('activate')
            ->with(7)
            ->willReturn(true);

        $model->expects($this->once())
            ->method('getById')
            ->with(7)
            ->willReturn([
                'id' => 7,
                'name' => 'Sangkat Srah Chak',
                'latitude' => '11.58091400',
                'longitude' => '104.90983200',
                'radius' => 100,
                'status' => 'active',
            ]);

        $result = $service->createLocation([
            'name' => 'Sangkat Srah Chak',
            'latitude' => '11.58091400',
            'longitude' => '104.90983200',
            'radius' => 100,
            'status' => 'active',
        ]);

        $this->assertSame(7, $result['id']);
        $this->assertSame('active', $result['location']['status']);
    }
}
