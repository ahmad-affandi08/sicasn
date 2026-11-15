<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

final readonly class HasilSimpanStatistik
{
    public function __construct(
        public int $baru,
        public int $duplikat,
    ) {}
}
