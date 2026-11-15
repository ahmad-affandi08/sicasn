<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

final readonly class HasilMuatWilayah
{
    public function __construct(
        public int $jumlahWilayah,
        public int $formasiDipetakan,
    ) {}
}
