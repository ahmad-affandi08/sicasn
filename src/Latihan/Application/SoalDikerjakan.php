<?php

declare(strict_types=1);

namespace Src\Latihan\Application;

final readonly class SoalDikerjakan
{
    public function __construct(
        public int $urutan,
        public int $soalId,
        public string $kategori,
        public string $subkategori,
        public string $pertanyaan,
        public array $opsi,
        public ?int $opsiDipilihId,
        public bool $ragu,
    ) {}
}
