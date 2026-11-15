<?php

declare(strict_types=1);

namespace Src\DataImport\Application;

final readonly class HasilUraiCsv
{
    public function __construct(
        public array $baris,
        public array $galat,
        public int $totalBaris,
        public ?string $galatBerkas,
    ) {}
}
