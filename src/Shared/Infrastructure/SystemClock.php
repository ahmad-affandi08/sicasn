<?php

declare(strict_types=1);

namespace Src\Shared\Infrastructure;

use DateTimeImmutable;
use Illuminate\Support\Facades\Date;
use Src\Shared\Application\Clock;

final class SystemClock implements Clock
{
    public function sekarang(): DateTimeImmutable
    {
        return Date::now()->toDateTimeImmutable();
    }
}
