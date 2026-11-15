<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

use Src\Formasi\Domain\TipeLokasi;

final readonly class BarisWilayah
{
    public function __construct(
        public string $kode,
        public string $nama,
        public TipeLokasi $tingkat,
    ) {}

    public function kodeProvinsi(): string
    {
        return explode('.', $this->kode)[0];
    }
}
