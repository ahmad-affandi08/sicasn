<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Pengaturan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SimpanSitusRequest;
use App\Support\DialogFlash;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Src\Pengaturan\Application\AmbilPengaturanSitus;
use Src\Pengaturan\Application\SimpanPengaturanSitus;

final class SitusController extends Controller
{
    public function tampil(AmbilPengaturanSitus $ambil): Response
    {
        return Inertia::render('Admin/Pengaturan/Situs', ['pengaturan' => ['satuSesi' => $ambil->handle()->satuSesi]]);
    }

    public function simpan(SimpanSitusRequest $request, SimpanPengaturanSitus $simpan): RedirectResponse
    {
        $simpan->handle($request->boolean('satu_sesi'), (int) $request->user()->getAuthIdentifier());

        return back()->with('dialog', DialogFlash::sukses('Pengaturan situs tersimpan', 'Perubahan berlaku mulai permintaan berikutnya.'));
    }
}
