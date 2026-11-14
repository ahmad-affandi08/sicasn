<?php

declare(strict_types=1);

namespace Src\Pengaturan\Application;

final readonly class InputPengaturanEmail
{
    public function __construct(
        public string $aktif,
        public ?string $cadangan,
        public string $pengirimNama,
        public string $pengirimAlamat,
        public ?string $alamatBalas,
        public array $konfigurasi,
    ) {}
}
