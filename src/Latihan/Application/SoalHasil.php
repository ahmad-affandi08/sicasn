<?php

declare(strict_types=1);

namespace Src\Latihan\Application;

final readonly class SoalHasil
{
    public function __construct(
        public int $urutan,
        public string $kategori,
        public string $subkategori,
        public string $pertanyaan,
        public string $pembahasan,
        public string $sumberNama,
        public ?string $sumberUrl,
        public string $lisensi,
        public array $opsi,
        public ?int $opsiDipilihId,
        public bool $ragu,
        public string $status,
    ) {}
}
