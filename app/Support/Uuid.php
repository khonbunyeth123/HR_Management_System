<?php

declare(strict_types=1);

namespace App\Support;

use Ramsey\Uuid\Uuid as RamseyUuid;

final class Uuid
{
    public static function v4(): string
    {
        return RamseyUuid::uuid4()->toString();
    }

    public static function isV4(string $uuid): bool
    {
        return (bool) preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            trim($uuid)
        );
    }
}
