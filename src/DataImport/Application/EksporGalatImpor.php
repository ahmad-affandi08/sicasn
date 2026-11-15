<?php

declare(strict_types=1);

namespace Src\DataImport\Application;

final readonly class EksporGalatImpor
{
    public function __construct(private ImportBatchRepository $batch) {}

    public function handle(int $batchId): ?string
    {
        if ($this->batch->cari($batchId) === null) {
            return null;
        }

        $baris = [TemplateCsvStatistik::keCsv(['baris', 'kolom', 'pesan', 'nilai'])];

        foreach ($this->batch->galat($batchId) as $galat) {
            $baris[] = TemplateCsvStatistik::keCsv([(string) $galat->baris, (string) $galat->kolom, $galat->pesan, (string) $galat->nilai]);
        }

        return implode("\n", $baris)."\n";
    }
}
