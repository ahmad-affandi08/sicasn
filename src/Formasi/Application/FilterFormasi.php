<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

final readonly class FilterFormasi
{
    public const URUTAN = ['rasio_terendah', 'kuota_terbanyak', 'pelamar_tersedikit', 'terbaru'];

    public const PER_HALAMAN = 20;

    public const HALAMAN_MAKS = 100;

    public function __construct(
        public ?string $cari = null,
        public array $jenjang = [],
        public array $jurusan = [],
        public ?int $provinsi = null,
        public ?int $wilayah = null,
        public ?int $instansi = null,
        public ?string $jenisFormasi = null,
        public ?string $tingkat = null,
        public string $urut = 'rasio_terendah',
        public bool $sertakanTanpaKualifikasi = false,
        public int $halaman = 1,
        public int $perHalaman = self::PER_HALAMAN,
    ) {}

    public function menyaringKualifikasi(): bool
    {
        return $this->jenjang !== [] || $this->jurusan !== [];
    }
}
