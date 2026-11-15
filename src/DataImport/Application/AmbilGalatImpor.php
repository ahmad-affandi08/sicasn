<?php

declare(strict_types=1);

namespace Src\DataImport\Application;

final readonly class AmbilGalatImpor
{
    public function __construct(private ImportBatchRepository $batch) {}

    public function handle(int $batchId): array
    {
        return $this->batch->galat($batchId);
    }
}
