<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

use Src\Formasi\Domain\AmbangPersaingan;

final readonly class DetailFormasi
{
    public function __construct(
        public RincianFormasi $rincian,
        public ?PeluangFormasi $peluang,
        public array $riwayat,
        public string $tautanResmi,
        public AmbangPersaingan $ambang,
    ) {}
}
