<?php

declare(strict_types=1);

namespace Src\Formasi\Infrastructure;

use Src\Formasi\Application\KebijakanFormasiAktif;
use Src\Formasi\Domain\JenisPengadaan;

final class ConfigKebijakanFormasiAktif implements KebijakanFormasiAktif
{
    public function aktif(): array
    {
        return array_values(array_filter(array_map(
            fn (string $nilai) => JenisPengadaan::tryFrom($nilai),
            (array) config('sicasn.jenis_pengadaan_aktif', ['cpns']),
        )));
    }
}
