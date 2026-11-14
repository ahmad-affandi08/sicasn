<?php

declare(strict_types=1);

namespace Src\Identity\Application;

use DateTimeImmutable;
use Src\Identity\Domain\Peran;

interface PenggunaRepository
{
    public function simpanBaru(
        PenggunaBaru $data,
        Peran $peran,
        string $passwordHash,
        bool $wajibGantiPassword,
        ?DateTimeImmutable $berlakuSampai,
        DateTimeImmutable $sekarang,
    ): int;

    public function cari(int $id): ?PenggunaDto;

    public function emailTerpakai(string $email): bool;

    public function ambilHashPassword(int $id): ?string;

    public function setPasswordSementara(int $id, string $passwordHash, DateTimeImmutable $berlakuSampai): void;

    public function setPasswordBaru(int $id, string $passwordHash): void;

    public function setAktif(int $id, bool $aktif, ?string $alasan, DateTimeImmutable $sekarang): void;

    public function halaman(?string $cari, ?bool $aktif, int $halaman, int $perHalaman): HalamanPengguna;

    public function ringkasan(): RingkasanPengguna;
}
