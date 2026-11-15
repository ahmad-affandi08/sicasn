<?php

declare(strict_types=1);

namespace Src\DataImport\Application;

final readonly class HasilAgregasiLampiran
{
    public function __construct(
        public string $csv,
        public int $barisCocok,
        public int $barisTidakCocok,
        public int $jumlahJabatan,
    ) {}
}
