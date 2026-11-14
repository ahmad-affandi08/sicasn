<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Pengaturan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SimpanPembayaranRequest;
use App\Support\DialogFlash;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Src\Pengaturan\Application\AmbilPengaturanPembayaran;
use Src\Pengaturan\Application\SimpanPengaturanPembayaran;

final class PembayaranController extends Controller
{
    public function tampil(AmbilPengaturanPembayaran $ambil): Response
    {
        $pengaturan = $ambil->handle();

        return Inertia::render('Admin/Pengaturan/Pembayaran', [
            'pengaturan' => [
                'rekening' => array_map(fn ($r) => ['bank' => $r->bank, 'nomor' => $r->nomor, 'atas_nama' => $r->atasNama], $pengaturan->rekening),
                'hargaTeks' => $pengaturan->hargaTeks,
                'catatan' => $pengaturan->catatan,
                'tampilkanRekeningPublik' => $pengaturan->tampilkanRekeningPublik,
            ],
        ]);
    }

    public function simpan(SimpanPembayaranRequest $request, SimpanPengaturanPembayaran $simpan): RedirectResponse
    {
        $simpan->handle(
            (array) $request->input('rekening', []),
            (string) $request->input('harga_teks'),
            (string) $request->input('catatan'),
            $request->boolean('tampilkan_rekening_publik'),
            (int) $request->user()->getAuthIdentifier(),
        );

        return back()->with('dialog', DialogFlash::sukses('Info pembayaran tersimpan', 'Harga dan catatan paket tampil di halaman Cara Akses.'));
    }
}
