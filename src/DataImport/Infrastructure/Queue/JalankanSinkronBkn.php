<?php

declare(strict_types=1);

namespace Src\DataImport\Infrastructure\Queue;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Src\DataImport\Application\SyncFormasiFromBkn;

final class JalankanSinkronBkn implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 1;

    public int $timeout = 1800;

    public function __construct(private readonly ?int $aktorId) {}

    public function handle(SyncFormasiFromBkn $sinkron): void
    {
        $sinkron->handle($this->aktorId);
    }
}
