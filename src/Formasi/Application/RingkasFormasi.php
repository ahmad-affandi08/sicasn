<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

final readonly class RingkasFormasi
{
    public function __construct(
        public int $id,
        public int $instansiId,
        public string $instansi,
        public string $jabatan,
        public string $kodeJabatan,
        public string $lokasi,
        public ?string $wilayah,
        public int $kuota,
        public string $jenisFormasi,
        public ?bool $disabilitas,
        public ?int $usiaMin,
        public ?int $usiaMaks,
        public bool $kualifikasiTersedia,
        public ?string $kualifikasi,
    ) {}
}
