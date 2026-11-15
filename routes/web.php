<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\DasborController;
use App\Http\Controllers\Admin\Pengaturan\EmailController;
use App\Http\Controllers\Admin\Pengaturan\PembayaranController;
use App\Http\Controllers\Admin\Pengaturan\SitusController;
use App\Http\Controllers\Admin\Pengaturan\WhatsappController;
use App\Http\Controllers\Admin\PenggunaController;
use App\Http\Controllers\Admin\SinkronController;
use App\Http\Controllers\AkunController;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\GantiPasswordController;
use App\Http\Controllers\KeluarController;
use App\Http\Controllers\MasukController;
use Illuminate\Support\Facades\Route;

Route::get('/', BerandaController::class)->name('beranda');

Route::middleware('guest')->group(function (): void {
    Route::get('/masuk', [MasukController::class, 'tampil'])->name('masuk');
    Route::post('/masuk', [MasukController::class, 'proses'])->name('masuk.proses');
});

Route::middleware(['auth', 'akun.aktif'])->group(function (): void {
    Route::post('/keluar', KeluarController::class)->name('keluar');
    Route::get('/ganti-password', [GantiPasswordController::class, 'tampil'])->name('ganti-password');
    Route::post('/ganti-password', [GantiPasswordController::class, 'proses'])->name('ganti-password.proses');
});

Route::middleware(['auth', 'akun.aktif', 'password.sudah-diganti', 'satu.sesi'])->group(function (): void {
    Route::get('/akun', AkunController::class)->name('akun');

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/', DasborController::class)->name('dasbor');

        Route::get('/pengguna', [PenggunaController::class, 'index'])->name('pengguna.index');
        Route::get('/pengguna/buat', [PenggunaController::class, 'buat'])->name('pengguna.buat');
        Route::post('/pengguna', [PenggunaController::class, 'simpan'])->name('pengguna.simpan');
        Route::get('/pengguna/{id}', [PenggunaController::class, 'tampil'])->whereNumber('id')->name('pengguna.tampil');
        Route::post('/pengguna/{id}/kirim-ulang', [PenggunaController::class, 'kirimUlang'])->whereNumber('id')->name('pengguna.kirim-ulang');
        Route::post('/pengguna/{id}/nonaktifkan', [PenggunaController::class, 'nonaktifkan'])->whereNumber('id')->name('pengguna.nonaktifkan');
        Route::post('/pengguna/{id}/aktifkan', [PenggunaController::class, 'aktifkan'])->whereNumber('id')->name('pengguna.aktifkan');

        Route::get('/sinkron', [SinkronController::class, 'tampil'])->name('sinkron');
        Route::post('/sinkron/jalankan', [SinkronController::class, 'jalankan'])->name('sinkron.jalankan');
        Route::put('/sinkron/pengaturan', [SinkronController::class, 'simpan'])->name('sinkron.simpan');
        Route::post('/sinkron/event-diberitahukan', [SinkronController::class, 'eventDiberitahukan'])->name('sinkron.event-diberitahukan');

        Route::prefix('pengaturan')->name('pengaturan.')->group(function (): void {
            Route::get('/email', [EmailController::class, 'tampil'])->name('email');
            Route::put('/email', [EmailController::class, 'simpan'])->name('email.simpan');
            Route::post('/email/uji', [EmailController::class, 'uji'])->name('email.uji');
            Route::get('/whatsapp', [WhatsappController::class, 'tampil'])->name('whatsapp');
            Route::put('/whatsapp', [WhatsappController::class, 'simpan'])->name('whatsapp.simpan');
            Route::get('/pembayaran', [PembayaranController::class, 'tampil'])->name('pembayaran');
            Route::put('/pembayaran', [PembayaranController::class, 'simpan'])->name('pembayaran.simpan');
            Route::get('/situs', [SitusController::class, 'tampil'])->name('situs');
            Route::put('/situs', [SitusController::class, 'simpan'])->name('situs.simpan');
        });
    });
});
