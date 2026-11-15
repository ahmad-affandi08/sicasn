<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

final readonly class OpsiPilihan
{
    public function __construct(
        public string $nilai,
        public string $label,
        public int $jumlah = 0,
    ) {}
}
