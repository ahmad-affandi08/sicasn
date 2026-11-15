<?php

declare(strict_types=1);

use Src\Shared\Application\PemeriksaanLingkungan;
use Src\Shared\Application\PemeriksaLingkungan;
use Src\Shared\Application\PeriksaLingkungan;

function pemeriksaPalsu(array $ekstensi, bool $database = true): PemeriksaLingkungan
{
    return new class($ekstensi, $database) implements PemeriksaLingkungan
    {
        public function __construct(private readonly array $ekstensi, private readonly bool $database) {}

        public function versiPhp(): PemeriksaanLingkungan
        {
            return new PemeriksaanLingkungan('Versi PHP', true, 'OK');
        }

        public function ekstensi(): array
        {
            return $this->ekstensi;
        }

        public function izinTulis(): array
        {
            return [new PemeriksaanLingkungan('Izin tulis storage', true, 'OK')];
        }

        public function koneksiDatabase(): PemeriksaanLingkungan
        {
            return new PemeriksaanLingkungan('Koneksi database', $this->database, 'x');
        }
    };
}

it('menggabungkan pemeriksaan versi, ekstensi, dan izin tulis sebelum database', function (): void {
    $periksa = new PeriksaLingkungan(pemeriksaPalsu([new PemeriksaanLingkungan('Ekstensi mbstring', true, 'OK')]));

    expect(array_map(fn (PemeriksaanLingkungan $butir) => $butir->nama, $periksa->sebelumDatabase()))
        ->toBe(['Versi PHP', 'Ekstensi mbstring', 'Izin tulis storage']);
});

it('menolak lanjut bila ada butir wajib yang gagal', function (): void {
    $periksa = new PeriksaLingkungan(pemeriksaPalsu([new PemeriksaanLingkungan('Ekstensi mbstring', false, 'Aktifkan')]));

    expect($periksa->semuaLolos($periksa->sebelumDatabase()))->toBeFalse();
});

it('mengabaikan butir tidak wajib yang gagal', function (): void {
    $periksa = new PeriksaLingkungan(pemeriksaPalsu([new PemeriksaanLingkungan('Opsional', false, 'Tidak masalah', false)]));

    expect($periksa->semuaLolos($periksa->sebelumDatabase()))->toBeTrue();
});

it('meneruskan hasil koneksi database', function (): void {
    expect((new PeriksaLingkungan(pemeriksaPalsu([], false)))->database()->lolos)->toBeFalse();
});
