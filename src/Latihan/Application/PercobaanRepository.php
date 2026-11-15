<?php

declare(strict_types=1);

namespace Src\Latihan\Application;

use DateTimeImmutable;

interface PercobaanRepository
{
    public function buat(int $penggunaId, string $mode, DateTimeImmutable $mulai, DateTimeImmutable $batas, array $soalIds, DateTimeImmutable $sekarang): int;

    public function idBelumSelesai(int $penggunaId): ?int;

    public function cari(int $penggunaId, int $id): ?RekamPercobaan;

    public function soalUntukDikerjakan(int $percobaanId): array;

    public function simpanJawaban(int $percobaanId, int $soalId, ?int $opsiId, bool $ragu): bool;

    public function opsiMilikSoal(int $soalId, int $opsiId): bool;

    public function ringkasSkor(int $percobaanId): array;

    public function tandaiSelesai(int $id, DateTimeImmutable $selesai, array $ringkasan): void;

    public function soalDenganKunci(int $percobaanId): array;

    public function riwayat(int $penggunaId, int $batas): array;

    public function idSoalTerbaru(int $penggunaId, int $jumlahPercobaan): array;
}
