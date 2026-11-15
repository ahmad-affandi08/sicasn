<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

use Src\Formasi\Domain\AmbangPersaingan;

final readonly class HasilPeluang
{
    public function __construct(
        public array $item,
        public int $total,
        public int $halaman,
        public int $perHalaman,
        public int $halamanTerakhir,
        public array $nasional,
        public AmbangPersaingan $ambang,
        public int $totalInstansi,
    ) {}
}
