<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require dirname(__DIR__, 2).'/vendor/autoload.php';

$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$perintah = $argv[1] ?? '';
$id = (int) ($argv[2] ?? 0);

if ($perintah === 'bersihkan') {
    DB::table('percobaan_soals')->delete();
    DB::table('percobaans')->delete();
    echo 'bersih', PHP_EOL;
}

if ($perintah === 'kunci') {
    $peta = [];

    foreach (DB::table('percobaan_soals')->where('percobaan_id', $id)->orderBy('urutan')->get() as $baris) {
        $opsi = DB::table('soal_opsis')->where('soal_id', $baris->soal_id)->orderByDesc('skor')->get();
        $peta[(int) $baris->urutan] = ['benar' => $opsi->first()->teks, 'salah' => $opsi->last()->teks];
    }

    echo json_encode($peta, JSON_UNESCAPED_UNICODE), PHP_EOL;
}

if ($perintah === 'kedaluwarsa') {
    DB::table('percobaans')->where('id', $id)->update(['batas_selesai_at' => now()->subMinute()->format('Y-m-d H:i:s')]);
    echo 'kedaluwarsa', PHP_EOL;
}

if ($perintah === 'skor') {
    echo json_encode(DB::table('percobaans')->find($id), JSON_UNESCAPED_UNICODE), PHP_EOL;
}
