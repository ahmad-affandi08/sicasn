<?php

declare(strict_types=1);

namespace Src\Shared\Application;

final readonly class PesanEmail
{
    public function __construct(
        public string $tujuan,
        public string $subjek,
        public string $tampilan,
        public array $data = [],
    ) {}
}
