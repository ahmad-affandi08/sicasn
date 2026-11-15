<?php

declare(strict_types=1);

namespace Src\Pantauan\Application;

use DateTimeImmutable;

interface PantauanRepository
{
    public function tambah(int $penggunaId, int $formasiId, DateTimeImmutable $sekarang): bool;

    public function hapus(int $penggunaId, int $formasiId): bool;

    public function ubahCatatan(int $penggunaId, int $formasiId, ?string $catatan, DateTimeImmutable $sekarang): bool;

    public function jumlah(int $penggunaId): int;

    public function daftar(int $penggunaId): array;

    public function idDipantau(int $penggunaId, array $formasiIds): array;
}
