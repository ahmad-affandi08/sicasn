<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

final readonly class HasilSimpanFormasi
{
    public function __construct(
        public int $baru,
        public int $diubah,
        public int $takBerubah,
    ) {}
}
