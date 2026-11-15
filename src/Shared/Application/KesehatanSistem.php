<?php

declare(strict_types=1);

namespace Src\Shared\Application;

use DateTimeImmutable;

final readonly class KesehatanSistem
{
    public function __construct(
        public int $antreanMenunggu,
        public int $antreanGagal,
        public int $emailGagal,
        public ?DateTimeImmutable $detakTerakhir,
        public bool $schedulerAktif,
    ) {}
}
