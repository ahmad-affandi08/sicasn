<?php

declare(strict_types=1);

namespace Src\DataImport\Application;

final readonly class DataPratinjau
{
    public function __construct(
        public string $namaBerkas,
        public array $baris,
        public array $galat,
        public int $totalBaris,
    ) {}
}
