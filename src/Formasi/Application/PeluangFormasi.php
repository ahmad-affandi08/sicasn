<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

use Src\Formasi\Domain\PenilaianPersaingan;

final readonly class PeluangFormasi
{
    public function __construct(
        public PenilaianPersaingan $penilaian,
        public PeluangMentah $dasar,
    ) {}

    public function pelamar(): ?int
    {
        return $this->dasar->pelamar ?? $this->dasar->submit;
    }
}
