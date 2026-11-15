<?php

declare(strict_types=1);

namespace Src\DataImport\Application;

use Src\Formasi\Application\StatistikRepository;

final readonly class AmbilRiwayatImporStatistik
{
    public function __construct(
        private ImportBatchRepository $batch,
        private StatistikRepository $statistik,
    ) {}

    public function handle(): array
    {
        return ['riwayat' => $this->batch->terbaru('csv_statistik', 20), 'totalBaris' => $this->statistik->jumlahBaris()];
    }
}
