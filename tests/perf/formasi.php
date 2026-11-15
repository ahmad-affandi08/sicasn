<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Src\Formasi\Application\FilterFormasi;
use Src\Formasi\Application\MuatReferensiWilayah;
use Src\Formasi\Application\SearchFormasi;

require dirname(__DIR__, 2).'/vendor/autoload.php';

$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$total = (int) ($argv[1] ?? 100000);
$ulang = (int) ($argv[2] ?? 30);

$pakaiUlang = ($argv[3] ?? '') === 'pakai-ulang';

if ($pakaiUlang) {
    Artisan::call('migrate', ['--force' => true]);
    goto ukur;
}

Artisan::call('migrate:fresh', ['--force' => true]);
$app->make(MuatReferensiWilayah::class)->handle();

mt_srand(42);
$waktu = date('Y-m-d H:i:s');

$namaInstansi = [];

for ($i = 1; $i <= 700; $i++) {
    $namaInstansi[] = ['nama' => "Instansi Uji {$i}", 'nama_normal' => "INSTANSI UJI {$i}", 'tipe' => $i % 3 === 0 ? 'daerah' : 'pusat', 'created_at' => $waktu, 'updated_at' => $waktu];
}

DB::table('instansis')->insert($namaInstansi);

$jabatan = [];

for ($i = 1; $i <= 3000; $i++) {
    $jabatan[] = ['kode' => "JF{$i}", 'nama' => "Jabatan Uji {$i}", 'nama_normal' => "JABATAN UJI {$i}", 'created_at' => $waktu, 'updated_at' => $waktu];
}

foreach (array_chunk($jabatan, 500) as $p) {
    DB::table('jabatans')->insert($p);
}

$jenjang = ['D3', 'D4', 'S1', 'S2', 'SMA'];
$jurusan = [];

for ($i = 1; $i <= 300; $i++) {
    $jurusan[] = ['nama' => "JURUSAN {$i}", 'nama_normal' => "JURUSAN {$i}", 'created_at' => $waktu, 'updated_at' => $waktu];
}

DB::table('jurusans')->insert($jurusan);

for ($s = 1; $s <= 400; $s++) {
    DB::table('kualifikasi_sets')->insert(['hash' => sha1("set{$s}"), 'teks' => "S-1 JURUSAN {$s}", 'created_at' => $waktu, 'updated_at' => $waktu]);
    $butir = [];

    foreach (array_rand(array_flip($jenjang), 2) as $j) {
        $butir[] = ['kualifikasi_set_id' => $s, 'jenjang' => $j, 'jurusan_id' => mt_rand(1, 10) === 1 ? null : mt_rand(1, 300), 'semua_jurusan' => false];
    }

    $butir[0]['semua_jurusan'] = mt_rand(1, 8) === 1;
    DB::table('kualifikasi_set_items')->insert($butir);
}

$wilayah = DB::table('wilayahs')->whereNotNull('kode')->get(['id', 'provinsi_id'])->all();
$baris = [];

for ($i = 1; $i <= $total; $i++) {
    $w = $wilayah[mt_rand(0, count($wilayah) - 1)];
    $set = mt_rand(1, 20) === 1 ? null : mt_rand(1, 400);
    $baris[] = [
        'external_id' => sprintf('00000000-0000-4000-9000-%012d', $i),
        'event_id' => 'evt-perf',
        'nama_event' => 'Uji Performa',
        'instansi_id' => mt_rand(1, 700),
        'jabatan_id' => mt_rand(1, 3000),
        'jenis_pengadaan' => 'cpns',
        'jenis_formasi' => mt_rand(1, 20) === 1 ? 'DISABILITAS' : 'UMUM',
        'periode' => 1,
        'lokasi_teks' => 'Uji',
        'tipe_lokasi' => 'kabupaten',
        'wilayah_id' => $w->id,
        'provinsi_id' => $w->provinsi_id,
        'kuota' => mt_rand(1, 200),
        'kualifikasi_set_id' => $set,
        'kualifikasi_tersedia' => $set !== null,
        'hash_baris' => sha1((string) $i),
        'first_seen_at' => date('Y-m-d H:i:s', time() - mt_rand(0, 86400 * 30)),
        'last_seen_at' => $waktu,
        'status' => 'aktif',
        'created_at' => $waktu,
        'updated_at' => $waktu,
    ];

    if (count($baris) === 1000) {
        DB::table('formasis')->insert($baris);
        $baris = [];
    }
}

$sekarang = new DateTimeImmutable($waktu);
$peluang = [];

for ($i = 1; $i <= 500; $i++) {
    $peluang[] = ['instansi_id' => $i, 'pelamar' => mt_rand(100, 500000), 'submit' => null, 'formasi' => mt_rand(10, 5000), 'formasi_dari_tanggal' => null, 'status_angka' => 'akhir', 'tingkat_data' => 'A', 'keyakinan' => 'turunan', 'tanggal_snapshot' => '2024-09-17', 'sumber_nama' => 'Uji', 'sumber_url' => null, 'rasio' => mt_rand(1, 3000) / 10, 'golongan_rasio' => 0, 'pelamar_dasar' => mt_rand(100, 500000), 'golongan_pelamar' => 0, 'created_at' => $waktu, 'updated_at' => $waktu];
}

DB::table('peluang_instansis')->insert($peluang);

ukur:
DB::connection()->getDriverName() === 'mysql'
    ? DB::statement('ANALYZE TABLE formasis, kualifikasi_set_items, kualifikasi_sets, peluang_instansis, jabatans, instansis')
    : DB::statement('ANALYZE');

$cari = $app->make(SearchFormasi::class);
$skenario = [
    'tanpa filter' => new FilterFormasi,
    'cari teks' => new FilterFormasi(cari: 'Jabatan Uji 12'),
    'jenjang S1' => new FilterFormasi(jenjang: ['S1']),
    'jenjang + jurusan' => new FilterFormasi(jenjang: ['S1'], jurusan: [5, 17, 99]),
    'provinsi' => new FilterFormasi(provinsi: (int) DB::table('wilayahs')->where('kode', '33')->value('id')),
    'kab/kota' => new FilterFormasi(wilayah: (int) DB::table('wilayahs')->where('kode', '33.74')->value('id')),
    'instansi' => new FilterFormasi(instansi: 12),
    'tingkat ketat' => new FilterFormasi(tingkat: 'ketat'),
    'gabungan' => new FilterFormasi(cari: 'Uji', jenjang: ['S1', 'D4'], provinsi: 11, tingkat: 'sedang', urut: 'kuota_terbanyak'),
    'urut kuota' => new FilterFormasi(urut: 'kuota_terbanyak'),
    'urut terbaru' => new FilterFormasi(urut: 'terbaru'),
    'urut pelamar' => new FilterFormasi(urut: 'pelamar_tersedikit'),
    'halaman maksimum' => new FilterFormasi(halaman: FilterFormasi::HALAMAN_MAKS),
    'halaman maks + urut kuota' => new FilterFormasi(halaman: FilterFormasi::HALAMAN_MAKS, urut: 'kuota_terbanyak'),
];

printf("%d formasi, %d pengulangan per skenario (%s)\n", $total, $ulang, DB::connection()->getDriverName());
printf("%-20s %8s %8s %8s %8s\n", 'skenario', 'total', 'p50 ms', 'p95 ms', 'maks ms');

$terburuk = 0.0;

foreach ($skenario as $nama => $filter) {
    $durasi = [];
    $hasil = null;

    for ($i = 0; $i < $ulang; $i++) {
        $mulai = hrtime(true);
        $hasil = $cari->handle($filter);
        $durasi[] = (hrtime(true) - $mulai) / 1e6;
    }

    sort($durasi);
    $p95 = $durasi[(int) floor(0.95 * (count($durasi) - 1))];
    $terburuk = max($terburuk, $p95);
    printf("%-20s %8d %8.1f %8.1f %8.1f\n", $nama, $hasil->total, $durasi[(int) (count($durasi) / 2)], $p95, end($durasi));
}

printf("\np95 terburuk: %.1f ms (target < 300 ms)\n", $terburuk);
exit($terburuk < 300 ? 0 : 1);
