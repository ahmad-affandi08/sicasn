<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\DasborController;
use App\Http\Controllers\Admin\Pengaturan\EmailController;
use App\Http\Controllers\Admin\Pengaturan\PembayaranController;
use App\Http\Controllers\Admin\Pengaturan\PersainganController;
use App\Http\Controllers\Admin\Pengaturan\SitusController;
use App\Http\Controllers\Admin\Pengaturan\WhatsappController;
use App\Http\Controllers\Admin\PenggunaController;
use App\Http\Controllers\Admin\SinkronController;
use App\Http\Controllers\Admin\StatistikController;
use App\Http\Controllers\AkunController;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\FormasiController;
use App\Http\Controllers\GantiPasswordController;
use App\Http\Controllers\KeluarController;
use App\Http\Controllers\MasukController;
use App\Http\Controllers\OpsiFormasiController;
use App\Http\Controllers\PantauanController;
use App\Http\Controllers\PeluangInstansiController;
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
    Route::get('/peluang-instansi', PeluangInstansiController::class)->name('peluang-instansi');
    Route::get('/formasi', [FormasiController::class, 'index'])->name('formasi');
    Route::get('/formasi/opsi/instansi', [OpsiFormasiController::class, 'instansi'])->middleware('throttle:120,1')->name('formasi.opsi.instansi');
    Route::get('/formasi/opsi/jurusan', [OpsiFormasiController::class, 'jurusan'])->middleware('throttle:120,1')->name('formasi.opsi.jurusan');
    Route::get('/formasi/opsi/provinsi/{id}/wilayah', [OpsiFormasiController::class, 'kabupatenKota'])->whereNumber('id')->middleware('throttle:120,1')->name('formasi.opsi.wilayah');
    Route::get('/formasi/{id}', [FormasiController::class, 'tampil'])->whereNumber('id')->name('formasi.tampil');

    Route::get('/pantauan', [PantauanController::class, 'index'])->name('pantauan');
    Route::get('/pantauan/bandingkan', [PantauanController::class, 'bandingkan'])->name('pantauan.bandingkan');
    Route::post('/pantauan', [PantauanController::class, 'simpan'])->middleware('throttle:60,1')->name('pantauan.simpan');
    Route::delete('/pantauan/{id}', [PantauanController::class, 'hapus'])->whereNumber('id')->name('pantauan.hapus');
    Route::put('/pantauan/{id}/catatan', [PantauanController::class, 'catatan'])->whereNumber('id')->middleware('throttle:60,1')->name('pantauan.catatan');

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

        Route::get('/statistik', [StatistikController::class, 'index'])->name('statistik.index');
        Route::get('/statistik/template', [StatistikController::class, 'templateCsv'])->name('statistik.template');
        Route::post('/statistik/pratinjau', [StatistikController::class, 'pratinjau'])->middleware('throttle:10,1')->name('statistik.pratinjau');
        Route::get('/statistik/pratinjau/{token}', [StatistikController::class, 'tampilPratinjau'])->where('token', '[A-Za-z0-9]{20,64}')->name('statistik.pratinjau.tampil');
        Route::post('/statistik/pratinjau/{token}/konfirmasi', [StatistikController::class, 'konfirmasi'])->where('token', '[A-Za-z0-9]{20,64}')->middleware('throttle:10,1')->name('statistik.konfirmasi');
        Route::post('/statistik/{id}/rollback', [StatistikController::class, 'rollback'])->whereNumber('id')->name('statistik.rollback');
        Route::get('/statistik/{id}/galat', [StatistikController::class, 'galat'])->whereNumber('id')->name('statistik.galat');

        Route::prefix('pengaturan')->name('pengaturan.')->group(function (): void {
            Route::get('/email', [EmailController::class, 'tampil'])->name('email');
            Route::put('/email', [EmailController::class, 'simpan'])->name('email.simpan');
            Route::post('/email/uji', [EmailController::class, 'uji'])->name('email.uji');
            Route::get('/whatsapp', [WhatsappController::class, 'tampil'])->name('whatsapp');
            Route::put('/whatsapp', [WhatsappController::class, 'simpan'])->name('whatsapp.simpan');
            Route::get('/pembayaran', [PembayaranController::class, 'tampil'])->name('pembayaran');
            Route::put('/pembayaran', [PembayaranController::class, 'simpan'])->name('pembayaran.simpan');
            Route::get('/persaingan', [PersainganController::class, 'tampil'])->name('persaingan');
            Route::put('/persaingan', [PersainganController::class, 'simpan'])->name('persaingan.simpan');
            Route::get('/situs', [SitusController::class, 'tampil'])->name('situs');
            Route::put('/situs', [SitusController::class, 'simpan'])->name('situs.simpan');
        });
    });
});
