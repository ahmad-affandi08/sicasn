<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Pengaturan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SimpanEmailRequest;
use App\Http\Requests\Admin\UjiEmailRequest;
use App\Support\DialogFlash;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Src\Pengaturan\Application\AmbilPengaturanEmail;
use Src\Pengaturan\Application\KirimEmailUji;
use Src\Pengaturan\Application\SimpanPengaturanEmail;
use Src\Pengaturan\Domain\Enkripsi;
use Src\Pengaturan\Domain\Exception\PengaturanTidakValid;
use Src\Pengaturan\Domain\PenyediaEmail;

final class EmailController extends Controller
{
    public function tampil(AmbilPengaturanEmail $ambil): Response
    {
        return Inertia::render('Admin/Pengaturan/Email', [
            'pengaturan' => (array) $ambil->handle(),
            'penyedia' => array_map(fn (PenyediaEmail $p) => [
                'kode' => $p->value,
                'label' => $p->label(),
                'preset' => [
                    'host' => $p->preset()->host,
                    'port' => $p->preset()->port,
                    'enkripsi' => $p->preset()->enkripsi->value,
                    'username' => $p->preset()->username,
                    'petunjuk' => $p->preset()->petunjuk,
                ],
            ], PenyediaEmail::cases()),
            'enkripsi' => array_map(fn (Enkripsi $e) => ['kode' => $e->value, 'label' => $e->label()], Enkripsi::cases()),
        ]);
    }

    public function simpan(SimpanEmailRequest $request, SimpanPengaturanEmail $simpan): RedirectResponse
    {
        try {
            $simpan->handle($request->keInput(), (int) $request->user()->getAuthIdentifier());
        } catch (PengaturanTidakValid $galat) {
            return back()->with('dialog', DialogFlash::gagal('Pengaturan belum bisa disimpan', $galat->getMessage()))->withInput();
        }

        return back()->with('dialog', DialogFlash::sukses('Pengaturan email tersimpan', 'Kirim email uji untuk memastikan semuanya berfungsi.'));
    }

    public function uji(UjiEmailRequest $request, KirimEmailUji $uji): RedirectResponse
    {
        $hasil = $uji->handle((string) $request->input('tujuan'), (int) $request->user()->getAuthIdentifier());

        if ($hasil->berhasil) {
            return back()->with('dialog', DialogFlash::sukses('Email uji terkirim', "Email uji berhasil dikirim lewat penyedia {$hasil->penyedia}. Periksa kotak masuk {$request->input('tujuan')}."));
        }

        return back()->with('dialog', DialogFlash::gagal('Email uji gagal dikirim', (string) $hasil->galat));
    }
}
