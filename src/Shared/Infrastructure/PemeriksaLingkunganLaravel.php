<?php

declare(strict_types=1);

namespace Src\Shared\Infrastructure;

use Illuminate\Support\Facades\DB;
use Src\Shared\Application\PemeriksaanLingkungan;
use Src\Shared\Application\PemeriksaLingkungan;
use Throwable;

final class PemeriksaLingkunganLaravel implements PemeriksaLingkungan
{
    private const PHP_MINIMAL = '8.3.0';

    private const EKSTENSI_WAJIB = ['ctype', 'curl', 'dom', 'fileinfo', 'json', 'mbstring', 'openssl', 'pdo', 'tokenizer', 'xml'];

    public function versiPhp(): PemeriksaanLingkungan
    {
        return new PemeriksaanLingkungan(
            'Versi PHP',
            version_compare(PHP_VERSION, self::PHP_MINIMAL, '>='),
            'Terpasang '.PHP_VERSION.', minimal '.self::PHP_MINIMAL.'.',
        );
    }

    public function ekstensi(): array
    {
        $hasil = array_map(
            fn (string $nama): PemeriksaanLingkungan => new PemeriksaanLingkungan(
                "Ekstensi {$nama}",
                extension_loaded($nama),
                extension_loaded($nama) ? 'Tersedia.' : "Aktifkan ekstensi {$nama} di PHP.",
            ),
            self::EKSTENSI_WAJIB,
        );

        $driver = extension_loaded('pdo_mysql');
        $hasil[] = new PemeriksaanLingkungan('Ekstensi pdo_mysql', $driver, $driver ? 'Tersedia.' : 'Aktifkan pdo_mysql agar PHP bisa terhubung ke MySQL.');

        return $hasil;
    }

    public function izinTulis(): array
    {
        return array_map(
            fn (string $folder): PemeriksaanLingkungan => new PemeriksaanLingkungan(
                "Izin tulis {$folder}",
                is_writable(base_path($folder)),
                is_writable(base_path($folder)) ? 'Bisa ditulis.' : "Folder {$folder} harus bisa ditulis oleh pengguna web server.",
            ),
            ['storage', 'bootstrap/cache'],
        );
    }

    public function koneksiDatabase(): PemeriksaanLingkungan
    {
        try {
            DB::connection()->getPdo();
        } catch (Throwable $galat) {
            return new PemeriksaanLingkungan('Koneksi database', false, 'Gagal terhubung: '.$this->ringkas($galat->getMessage()));
        }

        return new PemeriksaanLingkungan('Koneksi database', true, 'Terhubung ke '.DB::connection()->getDatabaseName().'.');
    }

    private function ringkas(string $pesan): string
    {
        return mb_substr(trim((string) preg_replace('/\s+/', ' ', $pesan)), 0, 200);
    }
}
