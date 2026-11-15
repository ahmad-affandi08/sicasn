<?php

declare(strict_types=1);

namespace Src\Latihan\Application;

final readonly class PercobaanBerjalan
{
    public function __construct(
        public int $id,
        public string $mode,
        public int $sisaDetik,
        public int $durasiDetik,
        public bool $selesai,
        public array $soal,
    ) {}
}
