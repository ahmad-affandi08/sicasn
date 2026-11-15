<?php

declare(strict_types=1);

namespace Src\Formasi\Domain;

enum TipeLokasi: string
{
    case Provinsi = 'provinsi';
    case Kabupaten = 'kabupaten';
    case Kota = 'kota';
    case SatuanKerja = 'satuan_kerja';
}
