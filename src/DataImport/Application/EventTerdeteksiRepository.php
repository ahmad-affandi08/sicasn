<?php

declare(strict_types=1);

namespace Src\DataImport\Application;

use DateTimeImmutable;

interface EventTerdeteksiRepository
{
    public function catat(array $event, DateTimeImmutable $sekarang): int;

    public function terbaru(int $jumlah): array;

    public function belumDiberitahukan(): array;

    public function tandaiDiberitahukan(DateTimeImmutable $sekarang): void;
}
