<?php

declare(strict_types=1);

namespace Src\Shared\Application;

use DateTimeImmutable;

interface Clock
{
    public function sekarang(): DateTimeImmutable;
}
