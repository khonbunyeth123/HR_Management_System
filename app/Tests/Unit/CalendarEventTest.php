<?php
declare(strict_types=1);

namespace App\Tests\Unit;

use App\Models\CalendarEvent;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class CalendarEventTest extends TestCase
{
    public function testNormalizeBooleanHandlesStringFalseValues(): void
    {
        $reflection = new ReflectionClass(CalendarEvent::class);
        $model = $reflection->newInstanceWithoutConstructor();
        $method = $reflection->getMethod('normalizeBoolean');
        $method->setAccessible(true);

        $this->assertSame(0, $method->invoke($model, 'false'));
        $this->assertSame(0, $method->invoke($model, '0'));
        $this->assertSame(1, $method->invoke($model, 'true'));
        $this->assertSame(1, $method->invoke($model, true));
    }
}
