<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Src\DataImport\Application\AgregasiLampiran;
use Src\DataImport\Application\MetadataLampiran;
use Src\DataImport\Application\PolaBarisLampiran;
use Src\DataImport\Domain\Exception\PolaLampiranTidakValid;
use Src\DataImport\Domain\Exception\TotalLampiranTidakSesuai;

final class AgregatLampiran extends Command
{
    protected $signature = 'sicasn:agregat-lampiran {berkas : teks hasil pdftotext -layout} {--pola= : regex dengan grup bernama jabatan dan lokasi opsional} {--instansi=} {--tanggal=} {--sumber=} {--url=} {--tahun=2024} {--status=akhir} {--total-resmi= : total peserta lulus menurut pengumuman} {--keluaran= : berkas CSV hasil}';

    protected $description = 'Menghitung peserta lulus seleksi administrasi per jabatan dari teks lampiran dan menulis CSV agregat tanpa data pribadi';

    public function handle(AgregasiLampiran $agregasi): int
    {
        $berkas = fopen((string) $this->argument('berkas'), 'rb');

        if ($berkas === false) {
            $this->error('Berkas teks tidak dapat dibaca.');

            return self::FAILURE;
        }

        try {
            $hasil = $agregasi->handle($this->baris($berkas), new PolaBarisLampiran((string) $this->option('pola')), $this->metadata());
        } catch (PolaLampiranTidakValid|TotalLampiranTidakSesuai $galat) {
            $this->error($galat->getMessage());

            return self::FAILURE;
        } finally {
            fclose($berkas);
        }

        file_put_contents((string) $this->option('keluaran'), $hasil->csv);
        $this->info("{$hasil->barisCocok} peserta dihitung pada {$hasil->jumlahJabatan} jabatan; {$hasil->barisTidakCocok} baris lain diabaikan. CSV ditulis ke {$this->option('keluaran')}.");

        return self::SUCCESS;
    }

    private function baris($berkas): iterable
    {
        while (($baris = fgets($berkas)) !== false) {
            yield rtrim($baris, "\r\n");
        }
    }

    private function metadata(): MetadataLampiran
    {
        $total = $this->option('total-resmi');

        return new MetadataLampiran(
            (string) $this->option('instansi'),
            (int) $this->option('tahun'),
            (string) $this->option('tanggal'),
            (string) $this->option('sumber'),
            $this->option('url') === null ? null : (string) $this->option('url'),
            (string) $this->option('status'),
            $total === null ? null : (int) $total,
        );
    }
}
