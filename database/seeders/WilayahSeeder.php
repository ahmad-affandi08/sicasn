<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\Formasi\Application\MuatReferensiWilayah;

final class WilayahSeeder extends Seeder
{
    public function run(MuatReferensiWilayah $muat): void
    {
        $muat->handle();
    }
}
