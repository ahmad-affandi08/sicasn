<?php

declare(strict_types=1);

namespace Src\DataImport\Application;

use Src\DataImport\Domain\Exception\BatchTidakBisaDibatalkan;
use Src\DataImport\Domain\StatusBatch;
use Src\Formasi\Application\StatistikRepository;
use Src\Shared\Application\AuditLog;
use Src\Shared\Application\Clock;

final readonly class RollbackBatchStatistik
{
    public function __construct(
        private ImportBatchRepository $batch,
        private StatistikRepository $statistik,
        private AuditLog $audit,
        private Clock $clock,
    ) {}

    public function handle(int $batchId, int $aktorId): int
    {
        $ringkas = $this->batch->cari($batchId);

        if ($ringkas === null || $ringkas->tipe !== 'csv_statistik') {
            throw BatchTidakBisaDibatalkan::tidakDitemukan();
        }

        if (! $ringkas->status->berhasil()) {
            throw BatchTidakBisaDibatalkan::statusTidakSesuai();
        }

        $dihapus = $this->statistik->hapusBatch($batchId);
        $sekarang = $this->clock->sekarang();

        $this->batch->selesai($batchId, StatusBatch::Dibatalkan, $ringkas->ringkasan + ['baris_dihapus' => $dihapus, 'dibatalkan_pada' => $sekarang->format('Y-m-d H:i:s')], $ringkas->hashRespons, $sekarang);
        $this->audit->catat($aktorId, 'statistik.dibatalkan', null, ['batch' => $batchId, 'baris_dihapus' => $dihapus]);

        return $dihapus;
    }
}
