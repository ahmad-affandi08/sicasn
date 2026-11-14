<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Pengaturan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SimpanWhatsappRequest;
use App\Support\DialogFlash;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Src\Pengaturan\Application\AmbilPengaturanWhatsapp;
use Src\Pengaturan\Application\SimpanPengaturanWhatsapp;
use Src\Pengaturan\Domain\Exception\NomorWhatsappTidakValid;

final class WhatsappController extends Controller
{
    public function tampil(AmbilPengaturanWhatsapp $ambil): Response
    {
        $pengaturan = $ambil->handle();

        return Inertia::render('Admin/Pengaturan/Whatsapp', [
            'pengaturan' => [
                'nomor' => $pengaturan->nomor?->digit ?? '',
                'namaTampilan' => $pengaturan->namaTampilan,
                'templatePesan' => $pengaturan->templatePesan,
            ],
            'pratinjauPesan' => $pengaturan->pesan(),
        ]);
    }

    public function simpan(SimpanWhatsappRequest $request, SimpanPengaturanWhatsapp $simpan): RedirectResponse
    {
        try {
            $simpan->handle((string) $request->input('nomor'), (string) $request->input('nama_tampilan'), (string) $request->input('template_pesan'), (int) $request->user()->getAuthIdentifier());
        } catch (NomorWhatsappTidakValid $galat) {
            return back()->withErrors(['nomor' => $galat->getMessage()])->withInput();
        }

        return back()->with('dialog', DialogFlash::sukses('Pengaturan WhatsApp tersimpan', 'Tombol chat di situs sekarang memakai nomor ini.'));
    }
}
