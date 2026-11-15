<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;
use Inertia\Testing\AssertableInertia as Assert;
use Src\Identity\Infrastructure\Persistence\UserModel;
use Src\Shared\Application\CatatDetakScheduler;

beforeEach(function (): void {
    $this->admin = UserModel::factory()->admin()->create();
});

it('menampilkan penjadwal belum terdeteksi sebelum ada detak', function (): void {
    $this->actingAs($this->admin)->get('/admin')->assertInertia(fn (Assert $halaman) => $halaman
        ->where('kesehatan.schedulerAktif', false)
        ->where('kesehatan.detakTerakhir', null)
        ->where('kesehatan.antreanMenunggu', 0)
        ->where('kesehatan.antreanGagal', 0)
        ->where('kesehatan.emailGagal', 0));
});

it('menandai penjadwal aktif setelah detak terbaru', function (): void {
    app(CatatDetakScheduler::class)->handle();

    $this->actingAs($this->admin)->get('/admin')->assertInertia(fn (Assert $halaman) => $halaman
        ->where('kesehatan.schedulerAktif', true)
        ->whereNot('kesehatan.detakTerakhir', null));
});

it('menganggap penjadwal mati bila detak lebih tua dari lima menit', function (): void {
    app(CatatDetakScheduler::class)->handle();
    $this->travel(6)->minutes();

    $this->actingAs($this->admin)->get('/admin')->assertInertia(fn (Assert $halaman) => $halaman->where('kesehatan.schedulerAktif', false));
});

it('menghitung antrean menunggu, antrean gagal, dan email gagal tujuh hari terakhir', function (): void {
    DB::table('jobs')->insert([
        ['queue' => 'default', 'payload' => '{}', 'attempts' => 0, 'available_at' => now()->timestamp, 'created_at' => now()->timestamp],
        ['queue' => 'default', 'payload' => '{}', 'attempts' => 0, 'available_at' => now()->timestamp, 'created_at' => now()->timestamp],
    ]);
    DB::table('failed_jobs')->insert(['uuid' => 'uuid-1', 'connection' => 'database', 'queue' => 'default', 'payload' => '{}', 'exception' => 'x', 'failed_at' => now()]);
    DB::table('email_logs')->insert([
        ['tipe' => 'kredensial', 'status' => 'gagal', 'created_at' => now(), 'updated_at' => now()],
        ['tipe' => 'kredensial', 'status' => 'gagal', 'created_at' => now()->subDays(10), 'updated_at' => now()->subDays(10)],
        ['tipe' => 'kredensial', 'status' => 'terkirim', 'created_at' => now(), 'updated_at' => now()],
    ]);

    $this->actingAs($this->admin)->get('/admin')->assertInertia(fn (Assert $halaman) => $halaman
        ->where('kesehatan.antreanMenunggu', 2)
        ->where('kesehatan.antreanGagal', 1)
        ->where('kesehatan.emailGagal', 1));
});

it('menjadwalkan detak setiap menit bersama antrean dan sinkron', function (): void {
    $nama = collect(Schedule::events())->map(fn ($acara) => $acara->description)->all();

    expect($nama)->toContain('detak-scheduler')->toContain('antrean')->toContain('sinkron-harian');
});
