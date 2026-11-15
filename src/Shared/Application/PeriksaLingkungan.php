<?php

declare(strict_types=1);

namespace Src\Shared\Application;

final readonly class PeriksaLingkungan
{
    public function __construct(private PemeriksaLingkungan $pemeriksa) {}

    public function sebelumDatabase(): array
    {
        return [$this->pemeriksa->versiPhp(), ...$this->pemeriksa->ekstensi(), ...$this->pemeriksa->izinTulis()];
    }

    public function database(): PemeriksaanLingkungan
    {
        return $this->pemeriksa->koneksiDatabase();
    }

    public function semuaLolos(array $pemeriksaan): bool
    {
        foreach ($pemeriksaan as $butir) {
            if ($butir->wajib && ! $butir->lolos) {
                return false;
            }
        }

        return true;
    }
}
