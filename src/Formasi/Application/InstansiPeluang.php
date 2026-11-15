<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

use Src\Formasi\Domain\PenilaianPersaingan;

final readonly class InstansiPeluang
{
    public function __construct(
        public int $instansiId,
        public string $nama,
        public string $tipe,
        public SnapshotStatistik $terbaru,
        public ?int $formasi,
        public ?string $formasiDipinjamDari,
        public PenilaianPersaingan $penilaian,
        public array $riwayat,
    ) {}
}
