<?php

declare(strict_types=1);

namespace Src\Latihan\Application;

final readonly class HasilMuatBankSoal
{
    public function __construct(
        public int $baru,
        public int $diperbarui,
        public int $dinonaktifkan,
    ) {}

    public function total(): int
    {
        return $this->baru + $this->diperbarui;
    }
}
