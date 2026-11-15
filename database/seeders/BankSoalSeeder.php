<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use RuntimeException;
use Src\Latihan\Application\MuatBankSoal;
use Src\Latihan\Domain\Exception\SoalTidakValid;

final class BankSoalSeeder extends Seeder
{
    private const BERKAS = ['twk.json', 'tiu.json', 'tkp.json'];

    public function run(): void
    {
        $baris = [];

        foreach (self::BERKAS as $nama) {
            $isi = json_decode((string) file_get_contents(__DIR__.'/data/soal/'.$nama), true);

            if (! is_array($isi)) {
                throw new RuntimeException("Berkas soal {$nama} tidak dapat dibaca.");
            }

            $baris = [...$baris, ...$isi];
        }

        try {
            $hasil = app(MuatBankSoal::class)->handle($baris);
        } catch (SoalTidakValid $galat) {
            throw new RuntimeException('Bank soal tidak valid: '.$galat->getMessage(), previous: $galat);
        }

        $this->command?->info("Bank soal: {$hasil->baru} baru, {$hasil->diperbarui} diperbarui, {$hasil->dinonaktifkan} dinonaktifkan.");
    }
}
