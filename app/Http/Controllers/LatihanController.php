<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\JawabSoalRequest;
use App\Http\Requests\MulaiLatihanRequest;
use App\Http\Resources\LatihanResource;
use App\Support\DialogFlash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Src\Latihan\Application\AmbilBerandaLatihan;
use Src\Latihan\Application\AmbilHasilPercobaan;
use Src\Latihan\Application\AmbilPercobaan;
use Src\Latihan\Application\MulaiPercobaan;
use Src\Latihan\Application\SelesaikanPercobaan;
use Src\Latihan\Application\SimpanJawaban;
use Src\Latihan\Domain\Exception\BankSoalKosong;
use Src\Latihan\Domain\Exception\JawabanTidakValid;
use Src\Latihan\Domain\Exception\PercobaanBelumSelesai;
use Src\Latihan\Domain\Exception\PercobaanSudahSelesai;
use Src\Latihan\Domain\Exception\PercobaanTidakDitemukan;
use Src\Latihan\Domain\ModeLatihan;

final class LatihanController extends Controller
{
    public function index(Request $request, AmbilBerandaLatihan $beranda): Response
    {
        return Inertia::render('Latihan/Index', LatihanResource::beranda($beranda->handle($this->penggunaId($request))));
    }

    public function mulai(MulaiLatihanRequest $request, MulaiPercobaan $mulai): RedirectResponse
    {
        try {
            $id = $mulai->handle($this->penggunaId($request), ModeLatihan::from((string) $request->input('mode')));
        } catch (BankSoalKosong $galat) {
            return back()->with('dialog', DialogFlash::peringatan('Soal belum tersedia', $galat->getMessage()));
        }

        return redirect()->route('latihan.kerjakan', $id);
    }

    public function kerjakan(Request $request, int $id, AmbilPercobaan $ambil): Response|RedirectResponse
    {
        try {
            $percobaan = $ambil->handle($this->penggunaId($request), $id);
        } catch (PercobaanTidakDitemukan) {
            abort(404);
        }

        return $percobaan->selesai
            ? redirect()->route('latihan.hasil', $id)
            : Inertia::render('Latihan/Kerjakan', ['percobaan' => LatihanResource::percobaan($percobaan)]);
    }

    public function jawab(JawabSoalRequest $request, int $id, SimpanJawaban $simpan): JsonResponse
    {
        try {
            $sisa = $simpan->handle($this->penggunaId($request), $id, (int) $request->input('soal_id'), $request->filled('opsi_id') ? (int) $request->input('opsi_id') : null, $request->boolean('ragu'));
        } catch (PercobaanTidakDitemukan) {
            abort(404);
        } catch (PercobaanSudahSelesai $galat) {
            return response()->json(['pesan' => $galat->getMessage()], 409);
        } catch (JawabanTidakValid $galat) {
            return response()->json(['pesan' => $galat->getMessage()], 422);
        }

        return response()->json(['sisaDetik' => $sisa]);
    }

    public function selesai(Request $request, int $id, SelesaikanPercobaan $selesaikan): RedirectResponse
    {
        try {
            $selesaikan->handle($this->penggunaId($request), $id);
        } catch (PercobaanTidakDitemukan) {
            abort(404);
        }

        return redirect()->route('latihan.hasil', $id);
    }

    public function hasil(Request $request, int $id, AmbilHasilPercobaan $hasil): Response|RedirectResponse
    {
        try {
            return Inertia::render('Latihan/Hasil', ['hasil' => LatihanResource::hasil($hasil->handle($this->penggunaId($request), $id))]);
        } catch (PercobaanTidakDitemukan) {
            abort(404);
        } catch (PercobaanBelumSelesai) {
            return redirect()->route('latihan.kerjakan', $id);
        }
    }
}
