<?php

declare(strict_types=1);

namespace Src\Shared\Application;

interface PemeriksaLingkungan
{
    public function versiPhp(): PemeriksaanLingkungan;

    public function ekstensi(): array;

    public function izinTulis(): array;

    public function koneksiDatabase(): PemeriksaanLingkungan;
}
