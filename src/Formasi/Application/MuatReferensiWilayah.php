<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

use Src\Shared\Application\Clock;

final readonly class MuatReferensiWilayah
{
    public function __construct(
        private SumberReferensiWilayah $sumber,
        private ReferensiWilayahRepository $repository,
        private Clock $clock,
    ) {}

    public function handle(): HasilMuatWilayah
    {
        $baris = PenguraiCsvWilayah::urai($this->sumber->isiCsv());
        $jumlah = $this->repository->simpanBanyak($baris, $this->clock->sekarang());

        return new HasilMuatWilayah($jumlah, $this->repository->petakanUlangFormasi());
    }
}
