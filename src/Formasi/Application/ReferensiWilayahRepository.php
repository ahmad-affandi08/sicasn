<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

use DateTimeImmutable;

interface ReferensiWilayahRepository
{
    public function simpanBanyak(array $baris, DateTimeImmutable $sekarang): int;

    public function jumlahReferensi(): int;

    public function petakanUlangFormasi(): int;
}
