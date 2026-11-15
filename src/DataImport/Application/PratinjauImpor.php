<?php

declare(strict_types=1);

namespace Src\DataImport\Application;

final readonly class PratinjauImpor
{
    public function __construct(
        public ?string $token,
        public string $namaBerkas,
        public int $totalBaris,
        public int $barisValid,
        public int $jumlahGalat,
        public array $galat,
        public array $ringkasanSumber,
        public ?string $galatBerkas,
    ) {}
}
