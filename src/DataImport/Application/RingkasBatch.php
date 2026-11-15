<?php

declare(strict_types=1);

namespace Src\DataImport\Application;

use Src\DataImport\Domain\StatusBatch;

final readonly class RingkasBatch
{
    public function __construct(
        public int $id,
        public string $tipe,
        public StatusBatch $status,
        public string $mulaiAt,
        public ?string $selesaiAt,
        public array $ringkasan,
        public ?string $hashRespons,
        public ?string $sumber,
    ) {}
}
