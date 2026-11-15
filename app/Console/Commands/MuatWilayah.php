<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Src\Formasi\Application\MuatReferensiWilayah;

final class MuatWilayah extends Command
{
    protected $signature = 'sicasn:muat-wilayah';

    protected $description = 'Memuat referensi provinsi dan kabupaten/kota (Kemendagri) lalu memetakan ulang formasi';

    public function handle(MuatReferensiWilayah $muat): int
    {
        $hasil = $muat->handle();

        $this->info("{$hasil->jumlahWilayah} wilayah dimuat, {$hasil->formasiDipetakan} formasi dipetakan ulang.");

        return self::SUCCESS;
    }
}
