<?php

declare(strict_types=1);

namespace Src\Identity\Application;

final readonly class AmbilRingkasanPengguna
{
    public function __construct(private PenggunaRepository $pengguna) {}

    public function handle(): RingkasanPengguna
    {
        return $this->pengguna->ringkasan();
    }
}
