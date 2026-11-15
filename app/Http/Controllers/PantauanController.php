<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BandingkanRequest;
use App\Http\Requests\CatatanPantauanRequest;
use App\Http\Requests\PantauFormasiRequest;
use App\Http\Resources\PantauanResource;
use App\Support\DialogFlash;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Src\Formasi\Application\PenyediaAmbangPersaingan;
use Src\Pantauan\Application\AmbilDaftarPantauan;
use Src\Pantauan\Application\BandingkanPantauan;
use Src\Pantauan\Application\HentikanPantauan;
use Src\Pantauan\Application\PantauFormasi;
use Src\Pantauan\Application\UbahCatatanPantauan;
use Src\Pantauan\Domain\BatasPantauan;

final class PantauanController extends Controller
{
    public function index(Request $request, AmbilDaftarPantauan $daftar, PenyediaAmbangPersaingan $ambang): Response
    {
        $batas = $ambang->ambil();

        return Inertia::render('Pantauan/Index', [
            'item' => array_map(PantauanResource::item(...), $daftar->handle($this->penggunaId($request))),
            'ambang' => ['longgarMaks' => $batas->longgarMaks, 'ketatMin' => $batas->ketatMin],
            'batas' => ['perbandingan' => BatasPantauan::MAKS_PERBANDINGAN, 'catatan' => BatasPantauan::MAKS_CATATAN, 'pantauan' => BatasPantauan::MAKS_PANTAUAN],
        ]);
    }

    public function simpan(PantauFormasiRequest $request, PantauFormasi $pantau): RedirectResponse
    {
        try {
            $pantau->handle($this->penggunaId($request), (int) $request->input('formasi_id'));
        } catch (DomainException $galat) {
            return back()->with('dialog', DialogFlash::peringatan('Formasi belum bisa dipantau', $galat->getMessage()));
        }

        return back();
    }

    public function hapus(Request $request, int $id, HentikanPantauan $hentikan): RedirectResponse
    {
        $hentikan->handle($this->penggunaId($request), $id);

        return back();
    }

    public function catatan(CatatanPantauanRequest $request, int $id, UbahCatatanPantauan $ubah): RedirectResponse
    {
        try {
            $ubah->handle($this->penggunaId($request), $id, $request->input('catatan'));
        } catch (DomainException $galat) {
            return back()->with('dialog', DialogFlash::gagal('Catatan belum bisa disimpan', $galat->getMessage()));
        }

        return back();
    }

    public function bandingkan(BandingkanRequest $request, BandingkanPantauan $bandingkan, PenyediaAmbangPersaingan $ambang): Response|RedirectResponse
    {
        try {
            $item = $bandingkan->handle($this->penggunaId($request), $request->ids());
        } catch (DomainException $galat) {
            return to_route('pantauan')->with('dialog', DialogFlash::peringatan('Perbandingan belum bisa dibuka', $galat->getMessage()));
        }

        $batas = $ambang->ambil();

        return Inertia::render('Pantauan/Bandingkan', [
            'item' => array_map(PantauanResource::item(...), $item),
            'ambang' => ['longgarMaks' => $batas->longgarMaks, 'ketatMin' => $batas->ketatMin],
        ]);
    }

    private function penggunaId(Request $request): int
    {
        return (int) $request->user()->getAuthIdentifier();
    }
}
