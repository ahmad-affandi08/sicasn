<?php

declare(strict_types=1);

namespace Src\DataImport\Infrastructure\Queue;

use Src\DataImport\Application\PemicuSinkron;

final class AntreanPemicuSinkron implements PemicuSinkron
{
    public function antrekan(?int $aktorId): void
    {
        JalankanSinkronBkn::dispatch($aktorId);
    }
}
