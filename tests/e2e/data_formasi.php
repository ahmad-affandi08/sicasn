<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Src\DataImport\Application\SyncFormasiFromBkn;

require dirname(__DIR__, 2).'/vendor/autoload.php';

$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

mt_srand(7);

$instansi = DB::table('instansis')->where('nama', 'like', 'Kementerian%')->orderBy('id')->limit(12)->pluck('nama')->all();
$instansi = array_merge($instansi, ['Instansi Tanpa Statistik Uji']);

$jabatan = ['Perawat Ahli Pertama', 'Dokter Ahli Pertama', 'Analis Kebijakan Ahli Pertama', 'Penyuluh Hukum Ahli Pertama', 'Pranata Komputer Ahli Pertama', 'Auditor Ahli Pertama', 'Arsiparis Terampil', 'Statistisi Ahli Pertama', 'Perencana Ahli Pertama', 'Pengelola Pengadaan Barang dan Jasa', 'Teknisi Elektromedis Terampil', 'Penghulu Ahli Pertama'];
$pendidikan = ['S-1 HUKUM / D-IV HUKUM', 'D-III KEPERAWATAN', 'S-1 KEDOKTERAN', 'S-1 TEKNIK INFORMATIKA / S-1 SISTEM INFORMASI', 'D-IV SEMUA JURUSAN / S-1 SEMUA JURUSAN', 'S-1 STATISTIKA', 'S-1 AKUNTANSI / D-IV AKUNTANSI', 'S-2 ADMINISTRASI PUBLIK', 'SLTA SEMUA JURUSAN', null];
$lokasi = ['Kab. Sleman', 'Kota Malang', 'Kab. Temanggung', 'Kota Bandung', 'Kab. Bandung', 'Prov. Jawa Tengah', 'Kota Surabaya', 'Kab. Banyumas', 'Kota Padang Sidimpuan', 'Kab. Tojo Una-Una', 'Prov. DKI Jakarta', 'Kota Palangka Raya', 'Kab. Atlantis', 'Balai Pelatihan Uji | Wilayah II'];

$baris = [];

for ($i = 1; $i <= 86; $i++) {
    $disabilitas = $i % 17 === 0;
    $baris[] = [
        'id' => sprintf('00000000-0000-4000-8800-%012d', $i),
        'event_id' => 'evt-cpns-e2e',
        'nama_event' => 'Seleksi CPNS Sintetis E2E (khusus uji)',
        'rekrutmen_id' => 'rek-e2e',
        'instansi' => $instansi[$i % count($instansi)],
        'jenis_pengadaan' => 'CPNS',
        'jenis_formasi' => $disabilitas ? 'Disabilitas' : 'UMUM',
        'periode' => 1,
        'jabatan_kode' => 'JF-E2E-'.($i % 12),
        'nama_jabatan' => $jabatan[$i % count($jabatan)],
        'group_pendidikan' => null,
        'nama_pendidikan' => $pendidikan[$i % count($pendidikan)],
        'lokasi_formasi' => $lokasi[$i % count($lokasi)],
        'is_disabilitas' => $disabilitas,
        'jumlah' => mt_rand(1, 40),
        'usia_min' => 18,
        'usia_max' => 35,
        'jobdesc' => $i % 5 === 0 ? "Melaksanakan tugas jabatan.\nMenyusun laporan berkala." : null,
        'penghasilan_min' => null,
        'penghasilan_max' => null,
        'persyaratan' => [
            ['id' => 's1', 'nama' => 'Ijazah asli sesuai kualifikasi pendidikan, dipindai berwarna.', 'mandatory' => 1],
            ['id' => 's2', 'nama' => 'Kartu Tanda Penduduk elektronik yang masih berlaku.', 'mandatory' => 1],
            ['id' => 's3', 'nama' => 'Surat keterangan sehat dari fasilitas kesehatan.', 'mandatory' => 0],
        ],
    ];
}

Http::fake(['*' => Http::response(['status' => 200, 'error' => false, 'message' => 'success', 'data' => $baris])]);

echo $app->make(SyncFormasiFromBkn::class)->handle(null)->pesan, PHP_EOL;
