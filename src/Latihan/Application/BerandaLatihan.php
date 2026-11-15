<?php

declare(strict_types=1);

namespace Src\Latihan\Application;

final readonly class BerandaLatihan
{
    public function __construct(
        public array $ketersediaan,
        public ?int $percobaanBerjalanId,
        public array $riwayat,
        public array $ambang,
    ) {}
}
