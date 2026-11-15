<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

use Src\Formasi\Domain\AmbangPersaingan;
use Src\Shared\Application\Clock;

final readonly class SegarkanPeluangInstansi
{
    public function __construct(
        private HitungPeluangInstansi $hitung,
        private PeluangInstansiRepository $repository,
        private Clock $clock,
    ) {}

    public function handle(): int
    {
        return $this->repository->ganti($this->hitung->handle(AmbangPersaingan::awal()), $this->clock->sekarang());
    }
}
