<?php

declare(strict_types=1);

namespace Src\Latihan\Domain;

final readonly class OpsiBaru
{
    public function __construct(
        public string $label,
        public string $teks,
        public int $skor,
    ) {}
}
