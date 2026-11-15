<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Pengaturan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SimpanPersainganRequest;
use App\Support\DialogFlash;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Src\Pengaturan\Application\AmbilPengaturanPersaingan;
use Src\Pengaturan\Application\SimpanPengaturanPersaingan;
use Src\Pengaturan\Domain\Exception\PengaturanTidakValid;

final class PersainganController extends Controller
{
    public function tampil(AmbilPengaturanPersaingan $ambil): Response
    {
        $ambang = $ambil->handle();

        return Inertia::render('Admin/Pengaturan/Persaingan', ['pengaturan' => ['longgarMaks' => $ambang->longgarMaks, 'ketatMin' => $ambang->ketatMin]]);
    }

    public function simpan(SimpanPersainganRequest $request, SimpanPengaturanPersaingan $simpan): RedirectResponse
    {
        try {
            $simpan->handle((float) $request->input('longgar_maks'), (float) $request->input('ketat_min'), (int) $request->user()->getAuthIdentifier());
        } catch (PengaturanTidakValid $galat) {
            return back()->with('dialog', DialogFlash::gagal('Pengaturan belum bisa disimpan', $galat->getMessage()));
        }

        return back()->with('dialog', DialogFlash::sukses('Ambang persaingan tersimpan', 'Lencana persaingan memakai ambang baru mulai sekarang.'));
    }
}
