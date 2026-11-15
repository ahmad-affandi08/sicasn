<?php

declare(strict_types=1);

use Database\Seeders\StatistikPelamarSeeder;
use Illuminate\Support\Facades\DB;

it('memuat seed statistik 2024 lengkap dengan sumber dan tanggal pada setiap baris', function (): void {
    $this->seed(StatistikPelamarSeeder::class);

    expect(jumlah('statistik_pelamars'))->toBeGreaterThan(50)
        ->and(DB::table('statistik_pelamars')->whereNull('sumber_nama')->orWhere('sumber_nama', '')->count())->toBe(0)
        ->and(DB::table('statistik_pelamars')->whereNull('tanggal_snapshot')->count())->toBe(0)
        ->and(DB::table('statistik_pelamars')->where('sumber_url', 'not like', 'https://%')->count())->toBe(0)
        ->and(DB::table('statistik_pelamars')->where('tahun', '!=', 2024)->count())->toBe(0);
});

it('mencocokkan total nasional pelamar 17 September 2024 dengan rilis BKN', function (): void {
    $this->seed(StatistikPelamarSeeder::class);

    $total = DB::table('statistik_pelamars')->where('cakupan', 'nasional')->where('tanggal_snapshot', '2024-09-17')->whereNotNull('jumlah_pelamar')->pluck('jumlah_pelamar')->unique()->all();

    expect($total)->toBe([3963832]);
});

it('idempoten: menjalankan seeder dua kali tidak menggandakan baris', function (): void {
    $this->seed(StatistikPelamarSeeder::class);
    $sebelum = [jumlah('statistik_pelamars'), jumlah('instansis')];

    $this->seed(StatistikPelamarSeeder::class);

    expect([jumlah('statistik_pelamars'), jumlah('instansis')])->toBe($sebelum);
});

it('tidak menyimpan nama atau NIK pribadi di CSV seed', function (): void {
    $isi = (string) file_get_contents(database_path('seeders/data/statistik_instansi_2024.csv'));

    expect(preg_match('/\b\d{16}\b/', $isi))->toBe(0);
});
