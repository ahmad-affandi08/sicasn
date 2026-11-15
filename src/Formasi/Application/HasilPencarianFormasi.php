<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

use Src\Formasi\Domain\AmbangPersaingan;

final readonly class HasilPencarianFormasi
{
    public function __construct(
        public array $item,
        public int $total,
        public int $halaman,
        public int $perHalaman,
        public int $halamanTerakhir,
        public int $totalAktif,
        public AmbangPersaingan $ambang,
        public bool $dibatasi = false,
    ) {}
}
