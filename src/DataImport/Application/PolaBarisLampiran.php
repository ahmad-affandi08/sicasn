<?php

declare(strict_types=1);

namespace Src\DataImport\Application;

use Src\DataImport\Domain\Exception\PolaLampiranTidakValid;

final readonly class PolaBarisLampiran
{
    public function __construct(public string $ekspresi)
    {
        if (@preg_match($ekspresi, '') === false) {
            throw PolaLampiranTidakValid::bukanRegex();
        }

        if (! str_contains($ekspresi, '(?<jabatan>') && ! str_contains($ekspresi, '(?P<jabatan>')) {
            throw PolaLampiranTidakValid::tanpaGrupJabatan();
        }
    }

    public function cocokkan(string $baris): ?array
    {
        if (preg_match($this->ekspresi, $baris, $cocok) !== 1) {
            return null;
        }

        $jabatan = preg_replace('/\s+/u', ' ', trim((string) ($cocok['jabatan'] ?? ''))) ?? '';
        $lokasi = preg_replace('/\s+/u', ' ', trim((string) ($cocok['lokasi'] ?? ''))) ?? '';

        return $jabatan === '' ? null : [$jabatan, $lokasi];
    }
}
