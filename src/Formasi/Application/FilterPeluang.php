<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

final readonly class FilterPeluang
{
    public const URUTAN = ['rasio_terendah', 'pelamar_tersedikit', 'formasi_terbanyak', 'nama'];

    public function __construct(
        public ?string $cari = null,
        public ?string $tipe = null,
        public ?string $tingkat = null,
        public string $urut = 'rasio_terendah',
        public int $halaman = 1,
        public int $perHalaman = 25,
    ) {}
}
