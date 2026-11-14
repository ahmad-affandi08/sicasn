<?php

declare(strict_types=1);

namespace Src\Identity\Application;

final readonly class PenggunaBaru
{
    public function __construct(
        public string $nama,
        public string $email,
        public ?string $noWhatsapp,
        public ?string $catatanPembayaran,
        public string $tautanLogin,
        public ?int $dibuatOleh,
    ) {}
}
