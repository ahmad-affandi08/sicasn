<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

final readonly class PastikanReferensiWilayah
{
    public function __construct(
        private ReferensiWilayahRepository $repository,
        private MuatReferensiWilayah $muat,
    ) {}

    public function handle(): void
    {
        if ($this->repository->jumlahReferensi() === 0) {
            $this->muat->handle();
        }
    }
}
