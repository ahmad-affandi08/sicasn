<?php

declare(strict_types=1);

namespace Src\Identity\Infrastructure\Queue;

use Src\Identity\Application\KredensialEmail;
use Src\Identity\Application\PengirimKredensial;
use Src\Shared\Application\LogEmail;

final readonly class AntreanPengirimKredensial implements PengirimKredensial
{
    public function __construct(private LogEmail $log) {}

    public function antrekan(KredensialEmail $kredensial): void
    {
        $logId = $this->log->mulai($kredensial->penggunaId, 'kredensial');

        KirimEmailKredensial::dispatch($logId, $kredensial);
    }
}
