<?php

declare(strict_types=1);

namespace Src\Pengaturan\Domain;

final readonly class Rekening
{
    public function __construct(
        public string $bank,
        public string $nomor,
        public string $atasNama,
    ) {}
}
