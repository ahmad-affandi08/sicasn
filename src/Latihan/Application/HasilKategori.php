<?php

declare(strict_types=1);

namespace Src\Latihan\Application;

final readonly class HasilKategori
{
    public function __construct(
        public string $kategori,
        public string $label,
        public int $jumlah,
        public int $skor,
        public int $skorMaks,
        public int $benar,
        public int $salah,
        public int $kosong,
        public int $ambangSetara,
        public bool $tercapai,
    ) {}
}
