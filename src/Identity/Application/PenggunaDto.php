<?php

declare(strict_types=1);

namespace Src\Identity\Application;

use Src\Identity\Domain\Peran;

final readonly class PenggunaDto
{
    public function __construct(
        public int $id,
        public string $nama,
        public string $email,
        public ?string $noWhatsapp,
        public Peran $peran,
        public bool $aktif,
        public ?string $catatanPembayaran,
        public ?string $alasanNonaktif,
        public bool $wajibGantiPassword,
        public string $dibuatAt,
        public ?string $lastLoginAt,
        public ?string $statusEmail,
        public ?string $galatEmail,
    ) {}
}
