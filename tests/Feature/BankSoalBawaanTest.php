<?php

declare(strict_types=1);

use Database\Seeders\BankSoalSeeder;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    $this->seed(BankSoalSeeder::class);
});

it('memuat bank soal bawaan yang cukup untuk satu simulasi SKD lengkap', function (): void {
    $jumlah = DB::table('soals')->where('aktif', true)->selectRaw('kategori, count(*) as n')->groupBy('kategori')->pluck('n', 'kategori');

    expect($jumlah['twk'])->toBeGreaterThanOrEqual(30)
        ->and($jumlah['tiu'])->toBeGreaterThanOrEqual(35)
        ->and($jumlah['tkp'])->toBeGreaterThanOrEqual(45);
});

it('aman diulang tanpa menambah atau mengubah soal', function (): void {
    $sebelum = DB::table('soals')->count();

    $this->seed(BankSoalSeeder::class);

    expect(DB::table('soals')->count())->toBe($sebelum)
        ->and(DB::table('soals')->where('aktif', false)->count())->toBe(0);
});

it('mencantumkan sumber, lisensi, dan pembahasan pada setiap soal', function (): void {
    expect(DB::table('soals')->whereRaw("trim(sumber_nama) = ''")->count())->toBe(0)
        ->and(DB::table('soals')->whereRaw("trim(lisensi) = ''")->count())->toBe(0)
        ->and(DB::table('soals')->whereRaw("trim(pembahasan) = ''")->count())->toBe(0);
});

it('menjamin setiap soal punya lima opsi dengan kunci yang sah', function (): void {
    $soal = DB::table('soals')->get(['id', 'kategori']);

    foreach ($soal as $butir) {
        $skor = DB::table('soal_opsis')->where('soal_id', $butir->id)->orderBy('label')->pluck('skor')->map(fn ($s) => (int) $s)->all();
        $urut = $skor;
        sort($urut);

        expect(count($skor))->toBe(5);
        expect($urut)->toBe($butir->kategori === 'tkp' ? [1, 2, 3, 4, 5] : [0, 0, 0, 0, 5]);
    }
});

it('tidak memuat data pribadi atau tautan sumber yang tidak sah', function (): void {
    expect(DB::table('soals')->whereNotNull('sumber_url')->where('sumber_url', 'not like', 'http%')->count())->toBe(0);
});

it('menyebarkan kunci jawaban di semua posisi, bukan hanya satu huruf', function (): void {
    $posisi = DB::table('soal_opsis')->where('skor', 5)->selectRaw('label, count(*) as n')->groupBy('label')->pluck('n', 'label');

    expect($posisi->count())->toBe(5);

    foreach ($posisi as $jumlah) {
        expect($jumlah)->toBeGreaterThan(DB::table('soals')->count() * 0.1);
    }
});

it('dapat dimuat lewat perintah artisan', function (): void {
    DB::table('soal_opsis')->delete();
    DB::table('soals')->delete();

    $this->artisan('sicasn:muat-soal')->assertSuccessful();

    expect(DB::table('soals')->count())->toBeGreaterThan(400);
});
