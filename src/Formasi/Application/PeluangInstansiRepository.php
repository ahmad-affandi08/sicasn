<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

use DateTimeImmutable;

interface PeluangInstansiRepository
{
    public function ganti(array $daftar, DateTimeImmutable $sekarang): int;

    public function semua(): array;
}
