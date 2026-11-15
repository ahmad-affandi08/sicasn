<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

use DateTimeImmutable;
use Src\Formasi\Domain\Cakupan;
use Src\Formasi\Domain\JenisPengadaan;

interface StatistikRepository
{
    public function simpanBanyak(array $baris, array $idInstansi, int $batchId, DateTimeImmutable $sekarang): HasilSimpanStatistik;

    public function hapusBatch(int $batchId): int;

    public function snapshot(int $tahun, JenisPengadaan $jenis, Cakupan $cakupan, ?int $instansiId = null): array;

    public function jumlahBaris(): int;
}
