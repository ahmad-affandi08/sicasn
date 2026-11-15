<?php

declare(strict_types=1);

namespace Src\Latihan\Application;

use DateTimeImmutable;
use Src\Latihan\Domain\KategoriSoal;

interface BankSoalRepository
{
    public function jumlahAktifPerKategori(): array;

    public function idAcak(KategoriSoal $kategori, int $jumlah, array $hindari): array;

    public function muat(array $soal, DateTimeImmutable $sekarang): HasilMuatBankSoal;
}
