<?php

declare(strict_types=1);

namespace Src\DataImport\Domain;

final readonly class PengaturanSinkron
{
    public const URL_AWAL = 'https://api-rekrutmen.bkn.go.id/portal/api/spf';

    public function __construct(
        public string $url,
        public FrekuensiSinkron $frekuensi,
    ) {}
}
