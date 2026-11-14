<?php

declare(strict_types=1);

namespace Src\Identity\Application;

final readonly class RingkasanPengguna
{
    public function __construct(
        public int $aktif,
        public int $nonaktif,
    ) {}
}
