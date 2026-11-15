<?php

declare(strict_types=1);

namespace Src\DataImport\Application;

final readonly class MetadataLampiran
{
    public function __construct(
        public string $instansi,
        public int $tahun,
        public string $tanggal,
        public string $sumberNama,
        public ?string $sumberUrl,
        public string $statusAngka,
        public ?int $totalResmi,
    ) {}
}
