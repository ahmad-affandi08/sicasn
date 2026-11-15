<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

use Src\Formasi\Domain\PenilaianPersaingan;

final readonly class PeluangJabatan
{
    public function __construct(
        public PenilaianPersaingan $penilaian,
        public SnapshotJabatan $dasar,
    ) {}
}
