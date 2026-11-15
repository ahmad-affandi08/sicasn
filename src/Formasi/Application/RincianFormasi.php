<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

final readonly class RincianFormasi
{
    public function __construct(
        public RingkasFormasi $ringkas,
        public string $status,
        public string $namaEvent,
        public string $jenisPengadaan,
        public int $periode,
        public string $tipeLokasi,
        public ?string $provinsi,
        public ?int $penghasilanMin,
        public ?int $penghasilanMaks,
        public ?string $uraianTugas,
        public array $kualifikasi,
        public array $persyaratan,
        public string $pertamaKaliTerlihat,
        public string $terakhirTerlihat,
    ) {}
}
