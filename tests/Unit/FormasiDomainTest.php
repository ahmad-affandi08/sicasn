<?php

declare(strict_types=1);

use Src\Formasi\Domain\JenisPengadaan;
use Src\Formasi\Domain\Lokasi;
use Src\Formasi\Domain\NamaNormal;
use Src\Formasi\Domain\PenguraiKualifikasi;
use Src\Formasi\Domain\TipeInstansi;
use Src\Formasi\Domain\TipeLokasi;

it('memetakan jenis pengadaan BKN secara toleran terhadap variasi penulisan', function (?string $masukan, JenisPengadaan $diharapkan): void {
    expect(JenisPengadaan::dariBkn($masukan))->toBe($diharapkan);
})->with([
    ['CPNS', JenisPengadaan::Cpns],
    ['cpns', JenisPengadaan::Cpns],
    ['  CPNS Reguler ', JenisPengadaan::Cpns],
    ['CPNS Kedinasan', JenisPengadaan::Cpns],
    ['PPPK Guru', JenisPengadaan::PppkGuru],
    ['PPPK Teknis', JenisPengadaan::PppkTeknis],
    ['PPPK Tenaga Kesehatan', JenisPengadaan::PppkNakes],
    ['PPPK Nakes', JenisPengadaan::PppkNakes],
    ['P3K Guru', JenisPengadaan::PppkGuru],
    ['PPPK', JenisPengadaan::Lainnya],
    ['Sekolah Kedinasan', JenisPengadaan::Lainnya],
    ['', JenisPengadaan::Lainnya],
    [null, JenisPengadaan::Lainnya],
]);

it('mengurai lokasi menjadi tipe dan nama', function (string $teks, TipeLokasi $tipe, string $nama): void {
    $lokasi = Lokasi::dari($teks);

    expect($lokasi->tipe)->toBe($tipe)->and($lokasi->nama)->toBe($nama);
})->with([
    ['Kab. Temanggung', TipeLokasi::Kabupaten, 'Temanggung'],
    ['Kabupaten Pangkajene Kepulauan', TipeLokasi::Kabupaten, 'Pangkajene Kepulauan'],
    ['Kota  Banjarbaru', TipeLokasi::Kota, 'Banjarbaru'],
    ['Prov. Jawa Tengah', TipeLokasi::Provinsi, 'Jawa Tengah'],
    ['Provinsi Bali', TipeLokasi::Provinsi, 'Bali'],
    ['Pusat Pendidikan, Pelatihan dan Pengembangan Profesi | Wilayah IV', TipeLokasi::SatuanKerja, 'Pusat Pendidikan, Pelatihan dan Pengembangan Profesi | Wilayah IV'],
]);

it('hanya memetakan lokasi bertipe wilayah ke tabel wilayah', function (): void {
    expect(Lokasi::dari('Kota Malang')->terpetakanKeWilayah())->toBeTrue()
        ->and(Lokasi::dari('Balai Besar X | Wilayah I')->terpetakanKeWilayah())->toBeFalse();
});

it('mengklasifikasi instansi secara konservatif', function (string $nama, TipeInstansi $tipe): void {
    expect(TipeInstansi::dariNama($nama))->toBe($tipe);
})->with([
    ['Kementerian Agama', TipeInstansi::Pusat],
    ['Kejaksaan Agung', TipeInstansi::Pusat],
    ['Pemerintah Kabupaten Sleman', TipeInstansi::Daerah],
    ['Pemerintah Provinsi Jawa Tengah', TipeInstansi::Daerah],
    ['Kota Surabaya', TipeInstansi::Daerah],
    ['Pemkab Bantul', TipeInstansi::Daerah],
    ['Badan Pusat Statistik', TipeInstansi::TidakDiketahui],
    ['Instansi Antah Berantah', TipeInstansi::TidakDiketahui],
]);

it('menormalkan nama untuk pencocokan', function (): void {
    expect(NamaNormal::dari("  Guru   Ahli\tPertama  "))->toBe('GURU AHLI PERTAMA')
        ->and(NamaNormal::rapikan('  Kab.   Sleman '))->toBe('Kab. Sleman')
        ->and(NamaNormal::dari(null))->toBe('');
});

it('mengurai kualifikasi pendidikan dari format BKN', function (): void {
    $hasil = PenguraiKualifikasi::urai('D-IV PSIKOLOGI / S-1 PSIKOLOGI / S-1 SEMUA JURUSAN');

    expect($hasil)->toHaveCount(3)
        ->and($hasil[0]->jenjang)->toBe('D4')
        ->and($hasil[0]->jurusan)->toBe('PSIKOLOGI')
        ->and($hasil[0]->semuaJurusan)->toBeFalse()
        ->and($hasil[2]->jenjang)->toBe('S1')
        ->and($hasil[2]->jurusan)->toBeNull()
        ->and($hasil[2]->semuaJurusan)->toBeTrue();
});

it('menyatukan potongan tanpa jenjang ke jurusan sebelumnya (jurusan yang memuat garis miring)', function (): void {
    $hasil = PenguraiKualifikasi::urai('S-1 PENDIDIKAN BAHASA / SASTRA INDONESIA / D-III AKUNTANSI');

    expect($hasil)->toHaveCount(2)
        ->and($hasil[0]->jurusan)->toBe('PENDIDIKAN BAHASA / SASTRA INDONESIA')
        ->and($hasil[1]->jenjang)->toBe('D3');
});

it('menormalkan penulisan jenjang yang beragam', function (string $teks, string $jenjang): void {
    expect(PenguraiKualifikasi::urai("{$teks} AKUNTANSI")[0]->jenjang)->toBe($jenjang);
})->with([
    ['D-I', 'D1'], ['D-II', 'D2'], ['D-III', 'D3'], ['D-IV', 'D4'], ['D4', 'D4'],
    ['S-1', 'S1'], ['S1', 'S1'], ['S-2', 'S2'], ['S-3', 'S3'],
    ['SMA/SMK/MA', 'SMA'], ['SMA', 'SMA'], ['SLTA', 'SMA'], ['SMP', 'SMP'], ['SD', 'SD'], ['PROFESI', 'PROFESI'],
]);

it('menghilangkan duplikat dan menangani masukan kosong', function (): void {
    expect(PenguraiKualifikasi::urai('S-1 HUKUM / S-1 HUKUM'))->toHaveCount(1)
        ->and(PenguraiKualifikasi::urai(null))->toBe([])
        ->and(PenguraiKualifikasi::urai('   '))->toBe([]);
});

it('menandai potongan tak dikenal sebagai jenjang LAINNYA tanpa menebak', function (): void {
    $hasil = PenguraiKualifikasi::urai('SERTIFIKAT KHUSUS');

    expect($hasil[0]->jenjang)->toBe('LAINNYA')->and($hasil[0]->jurusan)->toBe('SERTIFIKAT KHUSUS');
});
