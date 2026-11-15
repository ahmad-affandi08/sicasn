<?php

declare(strict_types=1);

namespace Src\DataImport\Application;

use Src\Shared\Application\AuditLog;

final readonly class AntrekanSinkron
{
    public function __construct(
        private PemicuSinkron $pemicu,
        private ImportBatchRepository $batch,
        private AuditLog $audit,
    ) {}

    public function handle(int $aktorId): bool
    {
        if ($this->batch->adaYangBerjalan('sinkron_bkn')) {
            return false;
        }

        $this->pemicu->antrekan($aktorId);
        $this->audit->catat($aktorId, 'sinkron.diminta', null);

        return true;
    }
}
