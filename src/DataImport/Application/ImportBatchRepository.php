<?php

declare(strict_types=1);

namespace Src\DataImport\Application;

use DateTimeImmutable;
use Src\DataImport\Domain\StatusBatch;

interface ImportBatchRepository
{
    public function mulai(string $tipe, ?string $sumber, ?int $dibuatOleh, DateTimeImmutable $sekarang): int;

    public function tutupYangKedaluwarsa(string $tipe, DateTimeImmutable $batas, DateTimeImmutable $sekarang): void;

    public function adaYangBerjalan(string $tipe): bool;

    public function selesai(int $id, StatusBatch $status, array $ringkasan, ?string $hashRespons, DateTimeImmutable $sekarang): void;

    public function catatGalat(int $id, array $galat): void;

    public function terakhirBerhasil(string $tipe): ?RingkasBatch;

    public function terbaru(string $tipe, int $jumlah): array;

    public function cari(int $id): ?RingkasBatch;

    public function galat(int $id): array;
}
