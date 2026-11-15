<?php

declare(strict_types=1);

namespace Src\DataImport\Infrastructure;

use Src\DataImport\Application\KebijakanJenisPengadaan;
use Src\Formasi\Domain\JenisPengadaan;

final class ConfigKebijakanJenisPengadaan implements KebijakanJenisPengadaan
{
    public function aktif(): array
    {
        return array_values(array_filter(array_map(
            fn (string $nilai) => JenisPengadaan::tryFrom($nilai),
            (array) config('sicasn.jenis_pengadaan_aktif', ['cpns']),
        )));
    }
}
