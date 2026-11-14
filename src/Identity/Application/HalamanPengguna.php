<?php

declare(strict_types=1);

namespace Src\Identity\Application;

final readonly class HalamanPengguna
{
    public function __construct(
        public array $item,
        public int $total,
        public int $halaman,
        public int $perHalaman,
        public int $halamanTerakhir,
    ) {}
}
