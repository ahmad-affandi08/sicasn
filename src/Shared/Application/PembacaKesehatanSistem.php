<?php

declare(strict_types=1);

namespace Src\Shared\Application;

use DateTimeImmutable;

interface PembacaKesehatanSistem
{
    public function antreanMenunggu(): int;

    public function antreanGagal(): int;

    public function emailGagalSejak(DateTimeImmutable $sejak): int;

    public function detakTerakhir(): ?DateTimeImmutable;

    public function catatDetak(DateTimeImmutable $sekarang): void;
}
