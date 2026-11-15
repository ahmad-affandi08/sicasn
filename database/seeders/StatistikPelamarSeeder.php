<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use RuntimeException;
use Src\DataImport\Application\KonfirmasiImporStatistik;
use Src\DataImport\Application\PratinjauImporStatistik;

final class StatistikPelamarSeeder extends Seeder
{
    private const BERKAS = __DIR__.'/data/statistik_instansi_2024.csv';

    public function run(): void
    {
        $pratinjau = app(PratinjauImporStatistik::class)->handle((string) file_get_contents(self::BERKAS), basename(self::BERKAS));

        if ($pratinjau->token === null || $pratinjau->jumlahGalat > 0) {
            throw new RuntimeException('Berkas statistik awal tidak valid: '.($pratinjau->galatBerkas ?? "{$pratinjau->jumlahGalat} baris bermasalah").'.');
        }

        $hasil = app(KonfirmasiImporStatistik::class)->handle($pratinjau->token, null);

        $this->command?->info("Statistik awal: {$hasil->baru} baris baru, {$hasil->duplikat} sudah ada.");
    }
}
