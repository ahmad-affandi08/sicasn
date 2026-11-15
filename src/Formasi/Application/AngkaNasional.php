<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

use Src\Formasi\Domain\Keyakinan;

final readonly class AngkaNasional
{
    public function __construct(
        public string $jenis,
        public int $nilai,
        public string $tanggal,
        public string $sumberNama,
        public ?string $sumberUrl,
        public Keyakinan $keyakinan,
    ) {}
}
