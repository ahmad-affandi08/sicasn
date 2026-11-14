<?php

declare(strict_types=1);

namespace Src\Shared\Application;

final readonly class CatatanAudit
{
    public function __construct(
        public string $aksi,
        public ?string $aktor,
        public array $rincian,
        public string $waktu,
    ) {}
}
