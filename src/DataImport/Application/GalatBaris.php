<?php

declare(strict_types=1);

namespace Src\DataImport\Application;

final readonly class GalatBaris
{
    public function __construct(
        public int $baris,
        public ?string $kolom,
        public string $pesan,
        public ?string $nilai,
    ) {}
}
