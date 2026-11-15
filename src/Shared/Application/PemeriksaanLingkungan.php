<?php

declare(strict_types=1);

namespace Src\Shared\Application;

final readonly class PemeriksaanLingkungan
{
    public function __construct(
        public string $nama,
        public bool $lolos,
        public string $keterangan,
        public bool $wajib = true,
    ) {}
}
