<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SimpanSinkronRequest;
use App\Http\Resources\SinkronResource;
use App\Support\DialogFlash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Src\DataImport\Application\AmbilStatusSinkron;
use Src\DataImport\Application\AntrekanSinkron;
use Src\DataImport\Application\SimpanPengaturanSinkron;
use Src\DataImport\Application\TandaiEventDiberitahukan;
use Src\DataImport\Domain\Exception\PengaturanSinkronTidakValid;

final class SinkronController extends Controller
{
    public function tampil(AmbilStatusSinkron $status): Response
    {
        return Inertia::render('Admin/Sinkron', SinkronResource::dari($status->handle()));
    }

    public function jalankan(Request $request, AntrekanSinkron $antrekan): RedirectResponse
    {
        if (! $antrekan->handle((int) $request->user()->getAuthIdentifier())) {
            return back()->with('dialog', DialogFlash::peringatan('Sinkron sedang berjalan', 'Sinkron sebelumnya belum selesai. Tunggu beberapa menit lalu muat ulang halaman ini.'));
        }

        return back()->with('dialog', DialogFlash::sukses('Sinkron dijadwalkan', 'Sinkron dimulai dalam sekitar satu menit. Muat ulang halaman ini untuk melihat hasilnya.'));
    }

    public function simpan(SimpanSinkronRequest $request, SimpanPengaturanSinkron $simpan): RedirectResponse
    {
        try {
            $simpan->handle((string) $request->input('url'), (string) $request->input('frekuensi'), (int) $request->user()->getAuthIdentifier());
        } catch (PengaturanSinkronTidakValid $galat) {
            return back()->with('dialog', DialogFlash::gagal('Pengaturan belum bisa disimpan', $galat->getMessage()));
        }

        return back()->with('dialog', DialogFlash::sukses('Pengaturan sinkron tersimpan', 'Jadwal dan alamat sumber data yang baru berlaku mulai sinkron berikutnya.'));
    }

    public function eventDiberitahukan(TandaiEventDiberitahukan $tandai): RedirectResponse
    {
        $tandai->handle();

        return back();
    }
}
