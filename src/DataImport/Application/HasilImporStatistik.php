<?php

declare(strict_types=1);

namespace Src\DataImport\Application;

final readonly class HasilImporStatistik
{
    public function __construct(
        public int $batchId,
        public int $baru,
        public int $duplikat,
        public int $galat,
    ) {}
}
