<?php

declare(strict_types=1);

namespace Src\Identity\Application;

final readonly class DaftarPengguna
{
    public function __construct(private PenggunaRepository $pengguna) {}

    public function handle(?string $cari, ?bool $aktif, int $halaman, int $perHalaman = 15): HalamanPengguna
    {
        return $this->pengguna->halaman($cari !== null && trim($cari) !== '' ? trim($cari) : null, $aktif, max(1, $halaman), $perHalaman);
    }
}
